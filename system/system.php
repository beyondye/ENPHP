<?php

namespace System;

//全局实例初始化数组
$instances = [];

//全局变量数组
$vars = [];

//定义常量
require_once APP_DIR . 'config/' . ENVIRONMENT . '/constans.php';

/**
 * 框架核心类
 * 
 * @author Ding <beyondye@gmail.com>
 */
class System
{

    /**
     * 加载类并返回类实例
     * 
     * @global array $instances 全局实例数组
     * 
     * @param string $name 类名称
     * @param string $namespace 类所在模块命名空间和目录路径一致
     * @param string $alias 实例别名
     * @param string|array $arguments 类构造函数参数
     * 
     * @return array 类实例数组
     */
    public function load($name, $namespace, $alias = '', $arguments = '')
    {
        global $instances;

        $name = ucfirst($name);
        //$namespace=ucwords(str_replace('/', '\\', $namespace),'\\');
        $namespace = str_replace('/', '\\', $namespace);

        $handler = $alias ? $alias : $name;

        //实例已存在直接返回
        if (isset($instances[$namespace][$handler])) {
            return $instances[$namespace][$handler];
        }

        $class = $namespace . '\\' . $name;

        if ('System\Database' == $namespace) {

            $config = include APP_DIR . 'config/' . ENVIRONMENT . '/database' . EXT;
            if (!isset($config[$alias])) {
                exit("{$name} '{$alias}' Config No Existed,Please Check Database Config File In '" . ENVIRONMENT . "' Directory.");
            }

            $arguments = $config[$alias];
        } else if ('System\Cache' == $namespace) {

            $config = include APP_DIR . 'config/' . ENVIRONMENT . '/redis' . EXT;
            if (!isset($config[$alias])) {
                exit("{$name} '{$alias}' Config No Existed,Please Check Redis Config File In '" . ENVIRONMENT . "' Directory.");
            }

            $arguments = $config[$alias];
        }

        //实例化并返回
        $instances[$namespace][$handler] = new $class($arguments);

        return $instances[$namespace][$handler];
    }

    /**
     * 覆盖__get
     * 
     * @param string $name 类名
     * 
     * @return object
     */
    public function __get($name)
    {
        if (in_array($name, array('input', 'config', 'output', 'session', 'lang', 'helper'))) {
            return $this->load($name, 'System');
        }

        if ($name == 'vars') {
            global $vars;
            return $vars;
        }

        if ($name == 'db') {
            return $this->load('database', 'System\\Database', 'default');
        }

        if ($name == 'redis') {
            return $this->load('redis', 'System\\Cache', 'default');
        }
    }

    /**
     * 通过方法返回数据库连接实例
     * 
     * @param string $service
     * 
     * @return object
     */
    public function db($service)
    {
        return $this->load('database', 'System\\Database', $service);
    }

    /**
     * 调用model
     * 
     * @param string $name
     * 
     * @return object
     */
    public function model($name)
    {
        return $this->load(str_replace('/', '\\', $name), 'Model');
    }

    /**
     * 调用redis
     * 
     * @param string $service
     * 
     * @return object
     */
    public function redis($service)
    {
        return $this->load('redis', 'System\Cache', $service);
    }

    /**
     * 加载语言包
     * 
     * @param string $lang
     * 
     * @return object
     */
    public function lang($lang)
    {
        return $this->load('lang', 'System', 'lang_' . $lang, $lang);
    }

}

//autoload  class
spl_autoload_register(function ($class) {

    $file = strtolower(str_replace('\\', '/', $class));
    $dirs = explode('/', $file);

    if ($dirs[0] == 'system') {
        unset($dirs[0]);
        $file = SYS_DIR . implode('/', $dirs) . EXT;
    } else {
        $file = APP_DIR . $file . EXT;
    }

    if (file_exists($file)) {
        include_once $file;
    }
});

//run application
$instances['system'] = new \System\System();

if (php_sapi_name() == 'cli') {
    $vars['controller'] = $_controller = isset($argv[1]) ? $argv[1] : DEFAULT_CONTROLLER;
    $vars['action'] = $_action = isset($argv[2]) ? $argv[2] : DEFAULT_ACTION;
} else {
    $vars['controller'] = $_controller = $instances['system']->input->get(CONTROLLER_KEY_NAME) ? $instances['system']->input->get(CONTROLLER_KEY_NAME) : DEFAULT_CONTROLLER;
    $vars['action'] = $_action = $instances['system']->input->get(ACTION_KEY_NAME) ? $instances['system']->input->get(ACTION_KEY_NAME) : DEFAULT_ACTION;
}

$instances['system']->load(str_replace('/', '\\', $_controller), 'Module\\' . ucfirst(MODULE))->$_action();

//echo '<pre>',var_dump($instances),'</pre>';
//close databases
if (isset($instances['database']) && count($instances['database']) > 0) {
    foreach ($instances['database'] as $rs) {
        $rs->close();
    }
}

//close cache
if (isset($instances['cache']) && count($instances['cache']) > 0) {
    foreach ($instances['cache'] as $rs) {
        $rs->close();
    }
}
//echo '<pre>',var_dump(get_included_files()),'</pre>';
