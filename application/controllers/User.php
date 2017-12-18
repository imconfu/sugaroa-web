<?php
/**
 * Created by PhpStorm.
 * User: confu
 * Date: 2017/9/6
 * Time: 上午12:52
 */

class UserController extends Controller
{
    public function indexAction()
    {
        return $this->loadView('/user');
    }

    public function listAction()
    {
        $params = [
            'account' => filter_input(INPUT_POST, 'account'),
            'mobile' => filter_input(INPUT_POST, 'mobile'),
            'realname' => filter_input(INPUT_POST, 'realname')
        ];
        $response = $this->restClient->users()->get(null, $params);
        $data = $this->getRestData($response);
        $result['total'] = $data['totalElements'];
        $result['rows'] = $data['content'];
        echo json_encode($result);
        return false;
    }

    public function saveAction()
    {
        $values = $_POST;
        $rest = $this->restClient->users();
        if (isset($values['id'])) {
            $rest = $rest->_($values['id']);
        }

        //有设置关联角色时转为整型数组
        if (isset($values['roles'])) {;
            //要注意为空时,explode里会有一个空元素，count($permissions)=1
            if (count($values['roles']) > 0) {
                $values['roles'] = array_map('intval', $values['roles']);
            } else {
                $values['roles'] = [];
            }
        }
        $response = $rest->post($values);
        $menu = $this->getRestData($response);

        echo json_encode($menu);
        return false;
    }

    public function detailAction()
    {
        $id = intval(filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT));
        $user = $this->getRestData($this->restClient->users()->_($id)->get());
        if(isset($user['roles'])){
            $user['roles[]'] = $user['roles'];
        }
        echo json_encode($user);
        return false;
    }


}