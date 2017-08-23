<?php
/**
 * Created by PhpStorm.
 * User: confu
 * Date: 2017/8/12
 * Time: 下午11:04
 */

use Lcobucci\JWT\Builder;


class AuthController extends Yaf_Controller_Abstract
{
    protected $session = array();

    public function init()
    {
        $this->session = Yaf_Session::getInstance();
    }

    public function indexAction()
    {
        $this->getView()->assign("redirect", filter_input(INPUT_GET, 'redirect'));
        $body = $this->getView()->render('auth.phtml');
        $this->getView()->assign("body", $body);
        $this->getView()->display('layout.phtml');
        return false;
    }

    public function loginAction()
    {
        $client = new SendGrid\Client('http://localhost:8080');
        $params = [
            'account' => filter_input(INPUT_POST, 'account'),
            'password' => filter_input(INPUT_POST, 'password')
        ];
        $response = $client->token()->grant()->post(null, $params);

        $result = json_decode($response->body(), true);

        if (isset($result['token'])) {
            $json = [
                'success' => true,
                'message' => '',
            ];
            $this->session->set('token', $result['token']);
        } else {
            $json = [
                'success' => false,
                'message' => $result['message'],
            ];

        }
        echo json_encode($json);
        return false;
    }


    //用户退出
    public function logoutAction()
    {
        $this->session->del('token');
        $this->redirect('/auth/index');
        return false;
    }
}