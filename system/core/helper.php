<?php

namespace System\Core;

/**
 * 帮助函数
 */
class Helper
{

    public function __get($name)
    {
        if ($name == 'system') {
            global $instances;
            return $instances['system'];
        }

        return $this->system->loadClass($name, '\Helper');
    }

    public function __call($name, $arguments)
    {
        $alias = 'alias_helper_' . uniqid('',true);
        return $this->system->loadClass($name, '\Helper', $alias, $arguments[0]);
    }

}
