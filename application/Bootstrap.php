<?php

class Bootstrap extends Yaf_Bootstrap_Abstract
{
    public function _initAutoload()
    {
        require APP_PATH.'/vendor/autoload.php';
    }
}