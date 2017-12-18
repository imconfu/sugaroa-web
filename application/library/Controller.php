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

        $this->restClient = new SendGrid\Client('http://localhost:8080', array("Authorization:Bearer " . $this->token));
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

    /**
     *
     * 判断是否有访问权限,包括用户权限
     * $throw_exception = true  会抛出异常,用于控制器捕捉处理
     * $throw_exception = false 用于视图中根据权限的处理，如某些功能按钮的显示与否
     *
     * 支持4种调用方法：
     * 1. accessPurview('code');                   //检查一个code权限,不检查mode，返回true/false
     * 3. accessPurview(array('code1','code2'));   //检查多个code权限,不检查mode，返回一维数组
     * 2. accessPurview('code', 1);                //检查某个code权限,检查一个mode，返回true/false
     * 4. accessPurview('code', array(1,2));       //检查某个code权限,检查多个mode，返回多维数组
     *
     * 注:如需要检查多个code的多个mode,重复调用方法4保存到一个数组中即可
     *
     * @param $userPurview
     * @param $code
     * @param int $mode
     * @param bool $throw_exception
     * @return array|bool
     * @throws Exception
     */
    public function access($userPurview, $code, $mode = 0, $throw_exception = true)
    {

        //获取用户权限总数
        $userPurviewCount = is_array($userPurview) ? count($userPurview) : -1;

        $check_result = array();

        $check_code = false;
        //检查多个code, mode参数忽略,返回一维数组
        if (is_array($code) && $mode == 0) {
            //遍历检查权限
            foreach ($code as $val) {
                //检查code权限
                $check_mode = false;
                if ($userPurviewCount == 0) {
                    $check_code = true;
                } else {
                    $check_code = (array_key_exists($val, $userPurview)) ? true : false;
                }

                if ($check_code) {
                    $check_result[$val] = true;
                } else {
                    if ($throw_exception) {
                        throw new Exception('没有访问权限!', 1001);
                    }
                    $check_result[$val] = false;
                }
            }

            return $check_result;
        } elseif (is_string($code) && is_array($mode)) { // 检查多个mode,code只能是字符串，返回二维数组
            foreach ($mode as $val) {
                $check_mode = false;
                //检查用户权限
                if ($userPurviewCount == 0) {
                    $check_code = true;
                    $check_mode = true;
                } else {
                    $check_code = (array_key_exists($code, $userPurview)) ? true : false;
                    //检查mode权限
                    if ($check_code) {
                        $check_mode = ($val == 0 || $userPurview[$code] == 0 || ($userPurview[$code] & $val)) ? true : false;
                    }
                }

                if ($check_code && $check_mode) {
                    $check_result[$code][$val] = true;
                } else {
                    if ($throw_exception) {
                        throw new Exception('没有访问权限!', 1002);
                    }
                    $check_result[$code][$val] = false;
                }
            }

            return $check_result;
        } elseif (is_string($code)) {

            $mode = intval($mode);
            //检查用户权限
            $check_mode = false;
            if ($userPurviewCount == 0) {
                $check_code = true;
                $check_mode = true;
            } else {
                if ($userPurview === null) {

                    $check_code = false;
                } else {
                    $check_code = (array_key_exists($code, $userPurview)) ? true : false;
                }
                //检查mode权限
                if ($check_code) {
                    $check_mode = ($mode == 0 || (!empty($userPurview[$code]) && $userPurview[$code] == 0) || (!empty($userPurview[$code]) && $userPurview[$code] & $mode) || count($userPurview) == 0) ? true : false;
                }
            }

            if ($check_code && $check_mode) {
                return true;
            } else {
                if ($throw_exception) {
                    throw new Exception('没有访问权限!', 1004);
                }
                return false;
            }
        } else {
            throw new Exception('检查权限调用参数不合法!', 1010);
        }
    }
}