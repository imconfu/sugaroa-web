<?php

defined('APP_PATH') or die('No direct script access.');

/**
 * 异常处理控制器
 *
 * @author confu
 */
class ErrorController extends Yaf_Controller_Abstract
{

    public function errorAction($exception)
    {
        $code = $exception->getCode();
        $message = $exception->getMessage();

        if ($this->getRequest()->isXmlHttpRequest()) {

            $json = ['error' => 'System Exception', 'code' => $code, 'message' => $message];
            echo json_encode($json);
            return false;
        } else {
            print_r($exception);
        }
    }
}