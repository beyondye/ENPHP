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


    $result = function () {


    };


    // 定义语言包查找路径的优先级
    $lang_paths = [
        LANG_DIR . $language . DIRECTORY_SEPARATOR . $file . EXT,      // 应用层当前语言
        SYS_DIR . 'locale' . DIRECTORY_SEPARATOR . LANG . DIRECTORY_SEPARATOR . $file . EXT  // 系统层默认语言
    ];

    if (isset($data[$lang_paths[0]])) {

        return $result($data[$lang_paths[0]]);


    } elseif (isset($data[$lang_paths[1]])) {
    }


    $path = ''; // 初始化路径为空字符串
    if (is_file($lang_paths[0])) {
        $path = $lang_paths[0];
    } elseif (is_file($lang_paths[1])) {
        $path = $lang_paths[1];
    } else {
        return $key; // 如果语言文件不存在，返回原始键
    }


    // 检查语言文件是否存在
    if (!isset($data[$path])) {
        $data[$path] = include $path;
    } elseif (!is_file($path)) {
        // 如果当前语言文件不存在，尝试使用默认语言
        $default_path = LANG_DIR . LANG . DIRECTORY_SEPARATOR . $file . EXT;
        if (!isset($data[$default_path]) && is_file($default_path)) {
            $data[$default_path] = include $default_path;
        }

        // 更新路径为默认语言路径
        $path = $default_path;
    }

    if (!isset($data[$path])) {
        return $key; // 如果语言文件不存在，返回原始键
    }

    // 支持多维数组访问 (例如: 'system.validator.required')
    $result = $data[$path];
    for ($i = 1; $i < count($keys); $i++) {
        if (is_array($result) && isset($result[$keys[$i]])) {
            $result = $result[$keys[$i]];
        } else {
            return $key; // 如果找不到指定的键，返回原始键
        }
    }

    // 如果结果仍然是数组，返回原始键
    if (is_array($result)) {
        return $result;
    }

    // 处理占位符替换
    $pattern = ['{:name}', '{name}'];
    $replacement = array_values($replace);

    return str_replace($pattern, $replacement, $result);
}
