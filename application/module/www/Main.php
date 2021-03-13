<?php

namespace module\www;

use system\Output;

class Main extends \system\Controller
{

    public function index()
    {
        $data['hello_world'] = 'hello wolrd';

        Output::view('main', $data);

    }

}
