<?php

class SystemController extends Controller
{
    public function indexAction()
    {
        $globalHeaders = array("Authorization: ".$this->session->get('token'));
        $client = new SendGrid\Client('http://localhost:8080', $globalHeaders);
        $params = [
            'account' => filter_input(INPUT_POST, 'account'),
            'password' => filter_input(INPUT_POST, 'password')
        ];
        $response = $client->menu()->getUserMenu()->get();

        print_r($response);
        $result = json_decode($response->body(), true);
        $json = [
            'success' => $result['success'],
            'message' => $result['message'],
        ];
        if ($result['success']) {

        }
        print_r($result);
        $this->getView()->assign("userMenu", $result['data']);
        return $this->loadView('/system');
    }
}