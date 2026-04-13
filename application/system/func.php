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

//lang('system.test',['test'=>'测试'],'en')
function lang(string $key, array $replace = [], string $lang = '')
{
    if (empty($key)) {
        return $key;
    }

    static $filedata = [];

    $language = $lang ?: \system\Lang::get(); //获取当前语言环境

    $keys = explode('.', $key);
    $file = $keys[0];

    // 递归替换占位符
    $placeholder = function (string $string) use ($replace) {

        if (empty($replace)) {
            return $string;
        }

        // 构建替换数组，处理单占位符格式
        $pairs = [];
        foreach ($replace as $key => $value) {
            $pairs['{' . $key . '}'] = $value;
        }

        // 执行替换
        return str_replace(array_keys($pairs), array_values($pairs), $string);
    };

    // 递归查找语言包中的值
    $result = function ($data) use ($keys, $key, $placeholder) {

        // 如果只有一个键（没有点号），返回整个语言文件数组
        if (count($keys) === 1) {
            if (is_array($data)) {
                foreach ($data as $k => $v) {
                    if (is_string($v)) {
                        $data[$k] = $placeholder($v);
                    }
                }
            }
            return $data;
        }

        for ($i = 1; $i < count($keys); $i++) {
            if (isset($data[$keys[$i]])) {
                $data = $data[$keys[$i]];
            } else {
                return $key; // 如果键不存在，返回原始键
            }
        }

        if (is_array($data)) {
            foreach ($data as $k => $v) {
                $data[$k] = $placeholder($v);
            }
            return $data;
        }

        if (is_string($data)) {
            return $placeholder($data);
        }

        return $key; // 如果数据既不是字符串也不是数组，返回原始键
    };

    // 定义语言包查找路径的优先级
    $paths = [
        LANG_DIR . $language . '/' . $file . EXT,      // 应用层当前语言
        SYS_DIR . 'locale/lang/' . $language . '/' . $file . EXT, // 系统层当前语言
    ];

    // 检查缓存
    foreach ($paths as $path) {
        if (isset($filedata[$path])) {
            return $result($filedata[$path]);
        }
    }

    // 检查文件并加载
    foreach ($paths as $path) {
        if (is_file($path)) {
            $filedata[$path] = include $path;
            return $result($filedata[$path]);
        }
    }


    return $key; // 如果语言文件不存在，返回原始键

}

function service(string $name): object
{
    $services = \system\Config::get($name);

    if ($services === null) {
        throw new \system\SysException("Service config not found:[{$name}]");
    }

    // 检查配置是否为数组
    if (!is_array($services)) {
        throw new \system\SysException("Service config must be an array:[{$name}]");
    }

    // 检查服务是否存在于配置中
    if (!isset($services[$name])) {
        throw new \system\SysException("Service config not found:[{$name}]");
    }

    $config = $services[$name];

    // 检查服务配置是否为数组
    if (!is_array($config)) {
        throw new \system\SysException("Service config must be an array:[{$name}]");
    }

    // 检查服务配置是否包含 entry 键
    if (!isset($config['entry'])) {
        throw new \system\SysException("Service config missing entry:[{$name}]");
    }

    /**
     * 递归构建器：采用闭包实现，支持无限层级类嵌套
     */
    $buildService = function ($item) use (&$buildService) {

        if (!is_array($item) || !array_key_exists('value', $item)) {
            return $item;
        }

        if (($item['type'] ?? '') === 'class') {
            $className = $item['value'];

            if (!class_exists($className)) {
                throw new \system\SysException("Class not found:[{$className}]");
            }

            $rawParams = $item['params'] ?? [];
            $resolvedArgs = array_map($buildService, $rawParams);

            return new $className(...array_values($resolvedArgs));
        }

        return $item['value'];
    };

    $args = array_map($buildService, $config['params'] ?? []);

    try {
        $instance = new $config['entry'](...array_values($args));
    } catch (Error $e) {
        throw new \system\SysException("Class not found:[{$config['entry']}]");
    }

    return $instance;
}