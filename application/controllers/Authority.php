<?php

class AuthorityController extends Controller
{
    public function indexAction()
    {
        return $this->loadView('/authority');
    }

    public function listAction()
    {
        $response = $this->restClient->authorities()->get();
        $data = $this->getRestData($response);
        echo json_encode($data['authorities']);
        return false;
    }

    public function propertyAction()
    {
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        $pid = filter_input(INPUT_GET, 'pid', FILTER_VALIDATE_INT);
        $code = filter_input(INPUT_GET, 'code');
        $type = filter_input(INPUT_GET, 'type');

        $rows = array();
        $rows[0] = ["name" => "分组名称", "value" => '', "key" => 'text', "group" => "基本信息", "editor" => "text"];


        echo json_encode(["total" => count($rows), "rows" => $rows]);
        return false;
    }


}