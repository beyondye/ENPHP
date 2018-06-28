<?php

namespace Inherit;

class Model extends \System\Model
{

    public function __construct()
    {

        parent::__construct();

        $this->RDB = 'default';
        $this->WDB = 'default';
    }

}
