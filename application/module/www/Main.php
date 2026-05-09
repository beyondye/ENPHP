<?php

namespace app\module\www;

use system\Output;

class Main extends \system\Controller
{
    public function index()
    {
        service('service.test');
        $data['hello_world'] = 'hello wolrd';

        Output::view('main', $data);

    }

}
