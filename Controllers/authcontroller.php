<?php

use App\Libraries\Controller;
use App\Libraries\Request;
use App\Libraries\Response;

class authcontroller extends Controller
{
    public function __construct()
    {
        $this->model("main");
    }
    public function index()
    {

        Response::set([
            'statusCode' => 200,
            'message' => "All ok"
        ]);
        exit();
    }
    public function register()
    {
        if (Request::getMethod() == 'POST') {
            $rawData = json_decode(file_get_contents("php://input"),true);            
            $this->model->register($rawData);
            exit();
        }
        Response::set([
            'statusCode' => 400,
            'status' => "Bad Request",
            'message' => 'Client error'
        ]);
        exit();
    }
    public function login()
    {
        if (Request::getMethod() == 'POST') {
            $rawData = json_decode(file_get_contents("php://input"),true);
            $this->model->login($rawData);
            exit();
        }
        Response::set([
            'statusCode' => 400,
            'status' => "Bad Request",
            'message' => 'Client error'
        ]);
        exit();
    }
}
