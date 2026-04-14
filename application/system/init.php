<?php

include APP_DIR . 'config/' . ENVIRONMENT . '/' . CONST_FILE . '.php';
include SYS_DIR . 'func.php';

//autoload class
spl_autoload_register(function ($class) {

    if (defined('VENDOR')) {
        foreach (VENDOR as $key => $val) {
            if (str_starts_with($class, $key)) {
                $suffix = substr($class, strlen($key));
                foreach ($val as $map) {
                    $file = str_replace('\\', DIRECTORY_SEPARATOR, $map . DIRECTORY_SEPARATOR . $suffix . EXT);
                    if (file_exists($file)) {
                        include $file;
                        return;
                    }
                }
            }
        }
    }

    $file = str_replace('\\', DIRECTORY_SEPARATOR, APP_DIR . $class . EXT);
    if (file_exists($file)) {
        include $file;
        return;
    }

    include SYS_DIR . str_replace(['\\', 'system'], ['/', ''],  $class) . EXT;
});

profiler('benchmark', 'running', 'Action');

\system\Config::init(AUTOLOAD_CONFIG_PATH);


define('CONTROLLER',\system\Input::get(CONTROLLER_KEY_NAME, DEFAULT_CONTROLLER));
define('ACTION', \system\Input::get(ACTION_KEY_NAME, DEFAULT_ACTION));

\system\Middleware::before();

if (preg_match('/^[\w\/]+$/', CONTROLLER) == 0 || preg_match('/^\w+$/', ACTION) == 0) {
    exit('Action Or Controller Not Found');
}

$controller = explode('/', CONTROLLER);
$controller[count($controller) - 1] = ucfirst(end($controller));
$ins = load('module\\' . MODULE . '\\' . join('\\', $controller));
if (!method_exists($ins, ACTION)) {
    exit('Action Not Found');
}

$act = ACTION;
$ins->$act();

\system\Middleware::after();

profiler('benchmark', 'running');

//echo '<pre>',var_dump(get_included_files()),'</pre>';
