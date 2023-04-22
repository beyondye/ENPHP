<?php

//global variable
$vars = [];

//include constant file
include APP_DIR . 'config/' . ENVIRONMENT . '/' . CONST_FILE . '.php';

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


//autoload class
spl_autoload_register(function ($class) {

    $file = '';
    $find = false;

    if (defined('VENDOR')) {
        foreach (VENDOR as $key => $val) {
            if (str_starts_with($class, $key)) {
                $suffix = substr($class, strlen($key));
                foreach ($val as $map) {
                    $file = str_replace('\\', DIRECTORY_SEPARATOR, $map . DIRECTORY_SEPARATOR . $suffix . EXT);
                    if (file_exists($file)) {
                        include $file;
                        $find = true;
                    }
                }
            }
        }
    }

    if ($find === false) {
        $file = str_replace('\\', DIRECTORY_SEPARATOR, APP_DIR . $class . EXT);
        if (file_exists($file)) {
            include $file;
        }
    }

});

profiler('benchmark', 'running', 'Action');

$vars['controller'] = \system\Input::get(CONTROLLER_KEY_NAME, DEFAULT_CONTROLLER);
$vars['action'] = \system\Input::get(ACTION_KEY_NAME, DEFAULT_ACTION);

define('CONTROLLER', $vars['controller']);
define('ACTION', $vars['action']);

\system\Middleware::before();

if (preg_match('/^[\w\/]+$/', $vars['controller']) == 0 || preg_match('/^\w+$/', $vars['action']) == 0) {
    exit('Action Or Controller Not Found');
}

$controller = explode('/', $vars['controller']);
$controller[count($controller) - 1] = ucfirst(end($controller));
$ins = load('module\\' . MODULE . '\\' . join('\\', $controller));
if (!method_exists($ins, $vars['action'])) {
    exit('Action Not Found');
}

$act = $vars['action'];
$ins->$act();

\system\Middleware::after();

profiler('benchmark', 'running');

//echo '<pre>',var_dump(get_included_files()),'</pre>';
