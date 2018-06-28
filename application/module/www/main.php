<?php

namespace Module\Employee;

class Main extends \Inherit\Controller
{

    function index()
    {
        $data['hello_world']='hello wolrd';
        $this->output->view('main',$data);
    }

}
