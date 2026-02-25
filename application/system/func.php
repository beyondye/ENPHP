<?php

//load single class
function load(string $class, $arguments = '', string $alias = '')
{
    static $instances;
    $alias = $class . '_as_' . $alias;
    if (isset($instances[$alias])) {
        return $instances[$alias];
    }

    if (!class_exists($class)) {
        exit($class . ' Not Found');
    }

    $instances[$alias] = new $class($arguments);
    return $instances[$alias];
}


//running profiler
function profiler(string $type, string $mark, string $desc = '')
{
    if (!defined('PROFILER')) {
        return false;
    }

    if (!PROFILER) {
        return false;
    }

    $profiler = \system\Profiler::instance();
    $profiler->$type($mark, $desc);

    return true;
}

//lang('system.test',['replace',],'en')
function lang(string $key, array $replace = [], string $lang = '')
{

    static $data = [];

    $language = $lang ?: \system\Lang::get(); //获取当前语言环境

    $keys = explode('.', $key);
    $file = $keys[0];

    $placeholder = function (string $string) use ($replace) {

        if (empty($replace)) {
            return $string;
        }

        // 构建替换数组，处理单占位符格式
        $pairs = [];
        foreach ($replace as $key => $value) {
            $pairs["{{$key}}"] = $value;
        }

        // 执行替换
        return str_replace(array_keys($pairs), array_values($pairs), $string);
    };

    $result = function ($data) use ($keys, $key, $placeholder) {

        for ($i = 1; $i < count($keys); $i++) {
            if (isset($data[$keys[$i]])) {
                $data = $data[$keys[$i]];
            } else {
                return $key; // 如果键不存在，返回原始键
            }
        }

        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = $placeholder($value);
            }
            return $data;
        }

        if (is_string($data)) {
            return $placeholder($data);
        }

        return $key;
    };


    // 定义语言包查找路径的优先级
    $paths = [
        LANG_DIR . $language . DIRECTORY_SEPARATOR . $file . EXT,      // 应用层当前语言
        SYS_DIR . 'locale' . DIRECTORY_SEPARATOR . LANG . DIRECTORY_SEPARATOR . $file . EXT  // 系统层默认语言
    ];

    if (isset($data[$paths[0]])) {
        return $result($data[$paths[0]]);
    }

    if (isset($data[$paths[1]])) {
        return $result($data[$paths[1]]);
    }

    if (is_file($paths[0])) {
        $data[$paths[0]] = include $paths[0];
        return $result($data[$paths[0]]);
    }

    if (is_file($paths[1])) {
        $data[$paths[1]] = include $paths[1];
        return $result($data[$paths[1]]);
    }

    return $key; // 如果语言文件不存在，返回原始键

}
