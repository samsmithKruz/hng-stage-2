<?php

use App\Libraries\Controller;
use App\Libraries\Response;

class indexcontroller extends Controller
{
    public function __construct()
    {
        $this->model("main");
        
    }
    public function index()
    {
        Response::set([
            'statusCode'=>200,
            'message'=>"All Ok"
        ]);
        exit();
    }
}
