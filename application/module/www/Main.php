<?php

namespace module\www;

class Main extends \inherit\Controller
{

    function index()
    {
        $data['hello_world']='hello wolrd';
        $this->output->view('main',$data);
    }

}
