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

    $lang = $lang ?: \system\Locale::lang();

    $keys = explode('.', $key);
    $path = LANG_DIR . $lang . DIRECTORY_SEPARATOR . $keys[0] . EXT;

    if (isset($data[$path])) {
        return $data[$path][$keys[1]];
    }

    $data[$path] = include $path;
    return $data[$path][$keys[1]];
}
