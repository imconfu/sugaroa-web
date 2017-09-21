<?php

class PermissionController extends Controller
{
    public function indexAction()
    {
        $response = $this->restClient->permissions()->pairs()->get();
        $pairs = $this->getRestData($response);
        $pairs[1] = '系统根权限';
        $this->getView()->assign("permissions", json_encode($pairs));
        return $this->loadView('/permission');
    }

    public function listAction()
    {
        $response = $this->restClient->permissions()->get();
        $data = $this->getRestData($response);
        echo json_encode($data);
        return false;
    }

    public function propertyAction()
    {
        $id = intval(filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT));
        $pid = filter_input(INPUT_POST, 'pid', FILTER_VALIDATE_INT);

        $rows = [];
        $rows[0] = ['name' => "权限名称", 'value' => '', 'key' => 'text', 'group' => "基本信息", 'editor' => 'text'];
        $rows[1] = [
            'name' => "所属上级", 'value' => $pid, 'key' => 'parentId', 'group' => "基本信息",
            'editor' => ($pid ? null : [
                'type' => "combotree",
                'options' => [
                    'method' => 'get',
                    'url' => '/permission/combotree',
                    'required' => true
                ],
            ])
        ];

        $rows[2] = ['name' => "权限表达式", 'value' => '', 'key' => 'expression', 'group' => "基本信息", 'editor' => 'text'];
        $rows[3] = ['name' => "请求路径", 'value' => '', 'key' => 'url', 'group' => "基本信息", 'editor' => 'text'];

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
        $response = $this->restClient->permissions()->_($id)->get();
        $permission = $this->getRestData($response);

        $rows[0]['value'] = $permission['text'];
        $rows[1]['value'] = $permission['parentId'];
        $rows[2]['value'] = $permission['expression'];
        $rows[3]['value'] = $permission['url'];
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

        $response = $this->restClient->permissions()->combotree()->get();
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
            $finalTree = [['id' => 1, 'text' => '系统权限', 'children' => $comboTree]];
        }
        echo json_encode($finalTree);
        return false;
    }

    public function saveAction()
    {
        $values = $_POST;
        $rest = $this->restClient->permissions();
        if (isset($values['id'])) {
            //编辑
            $rest = $rest->_($values['id']);
        }
        $response = $rest->post(null, $values);
        $permission = $this->getRestData($response);

        echo json_encode($permission);
        return false;
    }
}