<?php

class PrivilegeController extends Controller
{
    public function indexAction()
    {
        $response = $this->restClient->privileges()->pairs()->get();
        $pairs = $this->getRestData($response);
        $pairs[1] = '系统';
        $this->getView()->assign("privileges", json_encode($pairs));
        return $this->loadView('/privilege');
    }

    public function listAction()
    {
        $response = $this->restClient->privileges()->get();
        $data = $this->getRestData($response);
        echo json_encode($data);
        return false;
    }

    public function propertyAction()
    {
        $id = intval(filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT));
        $pid = filter_input(INPUT_POST, 'pid', FILTER_VALIDATE_INT);
        $resource = filter_input(INPUT_POST, 'resource');
        $type = filter_input(INPUT_POST, 'type');

        $rows = [];
        $rows[0] = ['name' => "分组名称", 'value' => '', 'key' => 'text', 'group' => "基本信息", 'editor' => 'text'];
        $rows[1] = [
            'name' => "所属上级", 'value' => $pid, 'key' => 'pid', 'group' => "基本信息",
            'editor' => ($pid ? null : [
                'type' => "combotree",
                'options' => [
                    'method' => 'get',
                    'url' => '/privilege/combotree',
                    'required' => true
                ],
            ])
        ];
        $rows[2] = ['name' => "排序", 'value' => 100, 'key' => 'sort', 'group' => "基本信息", 'editor' => 'text'];

        //权限资源信息
        if ($type == 'resource') {
            $rows[0]['name'] = ($resource ? '操作名称' : '资源名称');
            $rows[3] = ['name' => "资源代码", 'value' => $resource, 'key' => 'resource', 'group' => "权限资源", 'editor' => ($resource ? null : 'text')];
            $rows[4] = [
                'name' => "关联权限", 'value' => '', 'key' => 'relation', 'group' => "权限资源", 'editor' => [
                    'type' => "combotree",
                    'options' => [
                        'method' => 'get',
                        'multiple' => true,
                        'url' => '/privilege/combotree?multiple=-1'
                    ],
                ]
            ];
            $rows[5] = ['name' => "关联URL路径", 'value' => '', 'key' => 'action', 'group' => "权限资源", 'editor' => 'text'];
        }
        /**
         * 增加操作
         */
        if ($id == 0) {
            echo json_encode(["total" => count($rows), "rows" => $rows]);
            return false;
        }

        /**
         * 编辑前获取数据
         */
        $response = $this->restClient->privileges()->_($id)->get();
        $privilege = $this->getRestData($response);

        //print_r($privilege);
        // 对操作代码[operator]的编辑处理
        if ($privilege['resource'] && $privilege['operator'] < 2147483647) {
            $rows[0]['name'] = '操作名称';
            $rows[1]['editor'] = null; //不允许修改父级
        }
        $rows[0]['value'] = $privilege['text'];
        $rows[1]['value'] = $privilege['pid'];
        $rows[2]['value'] = $privilege['sort'];
        //在资源代码[resource]后面插入操作代码[operator]
        if ($type == 'resource') {
            $rows[3]['editor'] = null; //不允许修改资源代码
            $rows[3]['value'] = $privilege['resource'];
            //合成逗号隔开的字符串，方便控件直接使用
            if (isset($privilege['relation'])) {
                $rows[4]['value'] = join(',', $privilege['relation']);
            }
            $rows[5]['value'] = $privilege['action'];
        }
        echo json_encode(["total" => count($rows), "rows" => $rows]);
        return false;
    }

    /**
     * 获取简单的combotree用的数据
     *
     * @return array
     */
    public function combotreeAction()
    {
        $multiple = intval(filter_input(INPUT_GET, 'multiple'));

        $response = $this->restClient->privileges()->combotree()->get();
        $comboTree = $this->getRestData($response);

        if ($multiple == 1) {
            // [多选]显示所有权限，给用户分配权限时用
            // 因为：id不能用0,否则在treegrid中全选中后再单独取消选中某个，[所有权限]仍是选中状态。
            $finalTree = [['id' => 1, 'text' => '所有权限', 'children' => $comboTree]];
        } else if ($multiple == -1) {
            // [多选]不显示所有权限，权限选择关联权限时用
            $finalTree = $comboTree;
        } else {
            // [单选]权限选择父权限时用
            $finalTree = [['id' => 1, 'text' => '系统', 'children' => $comboTree]];
        }
        echo json_encode($finalTree);
        return false;
    }

    public function saveAction()
    {
        $values = $_POST;
        $rest = $this->restClient->privileges();
        if (isset($values['id'])) {
            //编辑
            $rest = $rest->_($values['id']);
        }
        $response = $rest->post(null, $values);
        $privilege = $this->getRestData($response);

        echo json_encode($privilege);
        return false;
    }
}