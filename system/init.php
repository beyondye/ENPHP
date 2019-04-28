<?php

//全局实例初始化数组
$instances = [];

//全局变量数组
$vars = [];

//定义常量
require_once APP_DIR . 'config/' . ENVIRONMENT . '/constans.php';


//autoload  class
spl_autoload_register(function ($class) {

    $file = str_replace('\\', '/', $class);
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
$instances['system\\System'] = $sys = new \system\System();

if (php_sapi_name() == 'cli') {
    $vars['controller'] = $_controller = isset($argv[1]) ? $argv[1] : DEFAULT_CONTROLLER;
    $vars['action'] = $_action = isset($argv[2]) ? $argv[2] : DEFAULT_ACTION;
} else {
    $vars['controller'] = $_controller = $sys->input->get(CONTROLLER_KEY_NAME, DEFAULT_CONTROLLER);
    $vars['action'] = $_action = $sys->input->get(ACTION_KEY_NAME, DEFAULT_ACTION);
}

if (preg_match('/^[\w\/]+$/', $_controller) == 0 || preg_match('/^\w+$/', $_action) == 0) {
    exit('Not Found Action Or Controller');
}

$contrs = explode('/', $_controller);
$contrs[count($contrs) - 1] = ucfirst(end($contrs));
$contrins = $sys->load('module\\' . MODULE.'\\'.join('\\', $contrs));

if (!method_exists($contrins, $_action)) {
    exit('Not Found Action');
}

$contrins->$_action();

//echo '<pre>',var_dump(get_included_files()),'</pre>';