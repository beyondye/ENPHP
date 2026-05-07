<?php
return [
    'test' =>function(){
        return new \app\service\Test(new \app\model\Test('database.default'));
    }
];
