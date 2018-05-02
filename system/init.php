<?php

//全局实例初始化数组
$instances = [];

//全局变量数组
$vars = [];

//定义常量
require_once APP_DIR . 'config/' . ENVIRONMENT . '/constans.php';


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
