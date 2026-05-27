<?php
declare(strict_types=1);

namespace App\Modules\Www;

use System\Output;

class Main extends \System\Controller
{
    public function index()
    {
        service('service.test');
        $data['hello_world'] = 'hello wolrd';

        Output::view('main', $data);

    }

}
