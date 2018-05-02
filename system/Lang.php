<?php

namespace System;

/**
 * 加载语言包
 *
 * @author Ding <beyondye@gmail.com>
 */
class Lang
{

    /**
     * 加载语言对应数据
     * 
     * @var array
     */
    public $mod = [];

    function __construct($lang = '')
    {
        if (!$lang) {
            $this->lang = LANG;
        } else {
            $this->lang = $lang;
        }
    }

    /**
     * 覆盖修改__get
     * 
     * @param sting $mod 语言模块名字
     * 
     * @return string
     */
    function __get($mod)
    {
        $name = $this->lang . '_' . $mod;

        if (isset($this->mod[$name])) {
            return $this->mod[$name];
        }

        $this->mod[$name] = include APP_DIR . 'language/' . $this->lang . '/' . $mod . EXT;
        return $this->mod[$name];
    }

}
