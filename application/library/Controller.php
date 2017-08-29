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
    protected $isXmlHttpRequest = false;
    protected $token = null;
    protected $restClient = null;

    public function init()
    {
        $this->session = Yaf_Session::getInstance();
        $this->isXmlHttpRequest = $this->getRequest()->isXmlHttpRequest();
        $this->token = $this->session->get('token');

        if ($this->token == null) {
            $location = '/auth/index';
            if ($this->isXmlHttpRequest) {
                echo json_encode(['error' => 'Login Expired', 'code' => -1000, 'message' => '登录过期！', 'location' => $location]);
                return false;
            } else {
                $this->redirect($location);
            }
        }

        $this->restClient = new SendGrid\Client('http://localhost:8080', array("Authorization: " . $this->token));
    }

    /**
     * 获取Rest请求结果
     *
     * @param $response
     * @return mixed
     * @throws Exception
     */
    public function getRestData($response)
    {
        if (!$response->statusCode()) {
            throw new Exception("请求无响应或超时！", -1001);
        }

        $result = json_decode($response->body(), true);

        if (isset($result['error'])) {
            throw new Exception($result['message'], $result['code']);
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
}