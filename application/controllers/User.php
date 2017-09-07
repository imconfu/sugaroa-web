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
        $response = $this->restClient->users()->get();
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
        $response = $rest->post(null, $values);
        $menu = $this->getRestData($response);

        echo json_encode($menu);
        return false;
    }

    public function detailAction()
    {
        $id = intval(filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT));
        $user = $this->getRestData($this->restClient->users()->_($id)->get());
        echo json_encode($user);
        return false;
    }


}