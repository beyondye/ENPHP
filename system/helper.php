<?php

namespace System;

/**
 * 函数
 */
class Helper
{

    public function __get($name)
    {
        global $instances;
        return $instances['system']->load($name, '\Helper');
    }
    
    
    public static function test(){
        
        
    }

}
