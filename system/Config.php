<?php

namespace system;

/**
 * 加载配置数据
 * 
 * @author Ding<beyondye@gmail.com>
 */
class Config
{

    /**
     * 配置数据
     * 
     * @var array
     */
    public $config = [];

    /**
     * 覆盖__get
     * 
     * @param array $name 配置文件名
     * @return array
     */
    function __get($name)
    {
        if (isset($this->config[$name])) {
            return $this->config[$name];
        } else {
            $this->config[$name] = include APP_DIR . 'config/' . $name . EXT;
            return $this->config[$name];
        }
    }

}
