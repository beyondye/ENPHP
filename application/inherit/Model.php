<?php

namespace inherit;

class Model extends \system\Model
{

    public function __construct()
    {

        parent::__construct();

        $this->RDB = 'default';
        $this->WDB = 'default';
    }

}
