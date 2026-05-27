<?php
return [
    'test' =>function(){
        return new \App\Services\Test(new \App\Models\Test('database.default'));
    }
];
