<?php

//global variable
$vars = [];
$sys = null;

//include constant file
if (CONST_FILE) {
    include APP_DIR . 'config/' . ENVIRONMENT . '/' . CONST_FILE . '.php';
} else {
    include APP_DIR . 'config/' . ENVIRONMENT . '/constant.php';
}

//load single class
function load(string $class, string $arguments = '', string $alias = '')
{
    static $instances;

    $alias = $class . '_as_' . $alias;

    if (isset($instances[$alias])) {
        return $instances[$alias];
    }

    if (!class_exists($class)) {
        exit(' Not Found ' . $class);
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

    if (false === PROFILER) {
        return false;
    }

    $profiler = \system\Profiler::instance();
    $profiler->$type($mark, $desc);

    return true;
}


//autoload class
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

profiler('benchmark', 'running', 'Action');

//run application
$sys = new \system\System();

if (php_sapi_name() == 'cli') {
    $vars['controller'] = $controller = isset($argv[1]) ? $argv[1] : DEFAULT_CONTROLLER;
    $vars['action'] = $action = isset($argv[2]) ? $argv[2] : DEFAULT_ACTION;
} else {
    $vars['controller'] = $controller = $sys->input->get(CONTROLLER_KEY_NAME, DEFAULT_CONTROLLER);
    $vars['action'] = $action = $sys->input->get(ACTION_KEY_NAME, DEFAULT_ACTION);
}

if (preg_match('/^[\w\/]+$/', $controller) == 0 || preg_match('/^\w+$/', $action) == 0) {
    exit('Not Found Action Or Controller');
}

$controller = explode('/', $controller);
$controller[count($controller) - 1] = ucfirst(end($controller));
$ins = load('module\\' . MODULE . '\\' . join('\\', $controller));

if (!method_exists($ins, $action)) {
    exit('Not Found Action');
}

$ins->$action();

profiler('benchmark', 'running');

//echo '<pre>',var_dump(get_included_files()),'</pre>';
