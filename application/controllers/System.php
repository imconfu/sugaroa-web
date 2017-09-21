<?php

class SystemController extends Controller
{
    public function indexAction()
    {
        $params = [
            'account' => filter_input(INPUT_POST, 'account'),
            'password' => filter_input(INPUT_POST, 'password')
        ];
        $response = $this->restClient->menus()->currentuser()->get();
        $userMenu = $this->getRestData($response);

        $this->getView()->assign("userMenu", $userMenu);
        return $this->loadView('/system');
    }

    public function mainAction()
    {
        return $this->loadView('/main');
    }
}