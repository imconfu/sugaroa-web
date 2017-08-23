<?php
defined('APP_PATH') or die('No direct script access.');

/**
 * Created by PhpStorm.
 * User: confu
 * Date: 2017/8/13
 * Time: 上午12:39
 */
class Controller extends Yaf_Controller_Abstract
{
    protected $session = array();
    protected $isAjax = false;
    protected $token = null;
    protected $restClient = null;
    protected $json = array(
        'success' => false,
        'total' => 0,
        'rows' => array(),//查询返回数组
        'code' => '',
        'message' => ''
    );

    public function init()
    {
        $this->session = Yaf_Session::getInstance();

        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->isAjax = true;
        }
        $this->session = Yaf_Session::getInstance();
        $this->token = $this->session->get('token');

        if ($this->token == null) {
            $this->json['code'] = -1000;
            $this->json['message'] = '登录过期，请重新登录！';

            return $this->forceLogout();
        }

        $this->restClient = new SendGrid\Client('http://localhost:8080', array("Authorization: ".$this->token));
    }

    public function getRestData($response){
        $result = json_decode($response->body(), true);
        if(!isset($result['success']) || $result['success']==true){
            $this->json['success'] = true;
        }

        if(isset($result['message'])){
            $this->json['message'] = $result['message'];
        }

        return $result;
    }
    /**
     * 默认的视图加载方法
     *
     * string  $viewFile  视图文件相对路径
     *
     */
    public function loadView($viewFile)
    {
        $viewPath = $this->getViewpath();
        $viewPathFile = $viewPath . $viewFile . '.phtml';

        if (!file_exists($viewPathFile)) {
            throw new Exception('不存在' . $viewFile . '视图');
        }
        $body = $this->getView()->render($viewPathFile);

        $this->getView()->assign("body", $body);
        $this->getView()->display(APP_PATH . '/application/views/layout.phtml');

        return false;
    }

    /**
     * 强制退出登录
     */
    public function forceLogout()
    {
        if ($this->isAjax === true) {
            $this->session->del('token');
            echo json_encode($this->json);
        } else {
            $this->redirect("/auth/logout");
        }
        exit;
    }
}