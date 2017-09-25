<?php

class RoleController extends Controller
{
    public function indexAction()
    {
        $response = $this->restClient->roles()->pairs()->get();
        $pairs = $this->getRestData($response);
        $pairs[1] = '顶级菜单';
        $this->getView()->assign("roles", json_encode($pairs));

        $response = $this->restClient->permissions()->pairs()->get();
        $pairs = $this->getRestData($response);

        $this->getView()->assign("permissions", json_encode($pairs));
        return $this->loadView('/role');
    }

    public function listAction()
    {
        $response = $this->restClient->roles()->get();
        $data = $this->getRestData($response);
        echo json_encode($data);
        return false;
    }

    /**
     * 同时返回property及权限数组
     * @return bool
     */
    public function detailAction()
    {
        $id = intval(filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT));
        $parentId = filter_input(INPUT_POST, 'parentId', FILTER_VALIDATE_INT);
        $result = [
            'property' => ['rows' => [], 'total' => 0],
            'permissions' => []
        ];
        $result['property']['rows'][0] = ['name' => "角色名称", 'value' => '', 'key' => 'title', 'group' => "基本信息", 'editor' => 'text'];
        $result['property']['rows'][1] = ['name' => "角色标识", 'value' => '', 'key' => 'code', 'group' => "基本信息", 'editor' => 'text'];

        $result['property']['total'] = count($result['property']['rows']);

        /**
         * 增加操作
         */
        if ($id == 0) {
            echo json_encode($result);
            return false;
        }

        /**
         * 编辑前获取数据
         */
        $response = $this->restClient->roles()->_($id)->get();
        $role = $this->getRestData($response);

        $result['property']['rows'][0]['value'] = $role['title'];
        $result['property']['rows'][1]['value'] = $role['code'];
        $result['permissions'] = $role['permissions'];

        echo json_encode($result);
        return false;
    }

    /**
     * 获取简单的combobox用的数据
     *
     * @return array
     */
    public function comboboxAction()
    {

        $response = $this->restClient->roles()->simplelist()->get();
        $comboBox = $this->getRestData($response);
        echo json_encode($comboBox);
        return false;
    }

    public function saveAction()
    {
        $values = $_POST;
        //有设置关联权限时转为数组
        if (isset($values['permissions'])) {;
            //要注意为空时,explode里会有一个空元素，count($permissions)=1
            if (count($values['permissions']) > 0) {
                $values['permissions'] = array_map('intval', $values['permissions']);
            } else {
                $values['permissions'] = [];
            }
        }
        $rest = $this->restClient->roles();
        if (isset($values['id'])) {
            //编辑
            $rest = $rest->_($values['id']);
        }

        $response = $rest->post($values);
        $role = $this->getRestData($response);
        echo json_encode($role);
        return false;
    }
}