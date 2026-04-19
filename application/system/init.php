<?php
require_once CONST_FILE;
require_once SYS_DIR . 'func.php';

spl_autoload_register(function ($class) {
    foreach (CLASS_MAP as $prefix => $dir) {
        if (strpos($class, $prefix) === 0) {
            $relativeClass = substr($class, strlen($prefix));
            $file = $dir . '/' . str_replace('\\', '/', $relativeClass) . '.php';
            if (file_exists($file)) {
                require $file;
                return true;
            }
        }
    }
    return false;
});


profiler('benchmark', 'running', 'Action');

\system\Config::init(AUTOLOAD_CONFIG_PATH);

define('CONTROLLER', \system\Input::get(CONTROLLER_KEY_NAME, DEFAULT_CONTROLLER));
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
