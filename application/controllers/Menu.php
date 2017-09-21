<?php

class MenuController extends Controller
{
    public function indexAction()
    {
        $response = $this->restClient->menus()->pairs()->get();
        $pairs = $this->getRestData($response);
        $pairs[1] = '顶级菜单';
        $this->getView()->assign("menus", json_encode($pairs));

        $response = $this->restClient->permissions()->pairs()->get();
        $pairs = $this->getRestData($response);

        $this->getView()->assign("permissions", json_encode($pairs));
        return $this->loadView('/menu');
    }

    public function listAction()
    {
        $response = $this->restClient->menus()->get();
        $data = $this->getRestData($response);
        echo json_encode($data);
        return false;
    }

    public function propertyAction()
    {
        $id = intval(filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT));
        $parentId = filter_input(INPUT_POST, 'parentId', FILTER_VALIDATE_INT);

        $rows = [];
        $rows[0] = ['name' => "名称", 'value' => '', 'key' => 'text', 'group' => "基本信息", 'editor' => 'text'];
        $rows[1] = [
            'name' => "所属上级", 'value' => 1, 'key' => 'parentId', 'group' => "基本信息",
            'editor' => ($parentId ? null : [
                'type' => "combotree",
                'options' => [
                    'method' => 'get',
                    'url' => '/menu/combotree',
                    'required' => true
                ],
            ])
        ];
        $rows[2] = ['name' => "链接地址", 'value' => '', 'key' => 'href', 'group' => "基本信息", 'editor' => 'text'];

        $rows[3] = [
            'name' => "关联权限", 'value' => '', 'key' => 'permissions', 'group' => "其它", 'editor' => [
                'type' => "combotree",
                'options' => [
                    'method' => 'get',
                    'multiple' => true,
                    'url' => '/permission/combotree?multiple=-1'
                ],
            ]
        ];

        $rows[4] = ['name' => "排序", 'value' => 100, 'key' => 'sort', 'group' => "其它", 'editor' => 'text'];

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
        $response = $this->restClient->menus()->_($id)->get();
        $menu = $this->getRestData($response);

        $rows[0]['value'] = $menu['text'];
        $rows[1]['value'] = $menu['parentId'];
        $rows[2]['value'] = $menu['href'];
        if (isset($menu['permissions'])) {
            $rows[3]['value'] = join(',', $menu['permissions']);
        }
        $rows[4]['value'] = $menu['sort'];
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

        $response = $this->restClient->menus()->combotree()->get();
        $comboTree = $this->getRestData($response);

        $finalTree = [['id' => 1, 'text' => '顶级菜单', 'children' => $comboTree]];
        echo json_encode($finalTree);
        return false;
    }

    public function saveAction()
    {
        $values = $_POST;
        //有设置关联权限时转为数组
        if (isset($values['permissions'])) {
            $values['permissions'] = trim($values['permissions']);
            $permissions = explode(',', $values['permissions']);
            //要注意为空时,explode里会有一个空元素，count($permissions)=1
            if ($values['permissions'] != '' && count($permissions) > 0) {
                $values['permissions'] = array_map('intval', $permissions);
            } else {
                $values['permissions'] = [];
            }
        }
        $rest = $this->restClient->menus();
        if (isset($values['id'])) {
            //编辑
            $rest = $rest->_($values['id']);
        }
        $response = $rest->post($values);
        $menu = $this->getRestData($response);
        echo json_encode($menu);
        return false;
    }
}