<?php
declare(strict_types=1);

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

/**
 * 错误转异常
 */
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno)) return false;
    throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
});

/**
 * 核心异常处理器
 */
$exceptionHandler = function (\Throwable $e) {
    $http = 500;
    $biz = 5000;
    $template = 'error/general';
    $log = false;

    foreach (EXCEPTION_MAP as $class => $config) {
        if ($e instanceof $class) {
            $http = $config['http'] ?? 500;
            $biz = $config['biz'] ?? 5000;
            $template = $config['template'] ?? 'error/general';
            $log = $config['log'] ?? false;
            break;
        }
    }

    if ($log) {
        $msg = sprintf("[%s] %s: %s in %s:%d\n%s\n", date('Y-m-d H:i:s'), get_class($e), $e->getMessage(), $e->getFile(), $e->getLine(), $e->getTraceAsString());
        @file_put_contents(LOG_FILE, $msg, FILE_APPEND);
    }

    if (ob_get_length()) ob_clean(); // 清除已有的输出缓冲

    \system\Output::status($http);

    if (\system\Input::isAjax()) {
        $msg = ($http >= 500) ? 'Internal Server Error' : $e->getMessage();
        \system\Output::json($biz, $msg);
    }

    // 页面响应
    if ($http < 500) {
        \system\Output::error($http, $template, ['exception' => $e]);
    } else {
        \system\Output::error($http, $template);
    }
};
set_exception_handler($exceptionHandler);

/**
 * 致命错误捕获
 */
register_shutdown_function(function () use ($exceptionHandler) {
    $lastError = error_get_last();

    // 只处理会导致脚本停止的致命错误
    $fatalErrors = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR];

    if ($lastError && in_array($lastError['type'], $fatalErrors)) {
        $e = new \ErrorException(
            $lastError['message'],
            0,
            $lastError['type'],
            $lastError['file'],
            $lastError['line']
        );
        // 手动触发异常处理器，确保致命错误也能返回 JSON 或 500 页面
        $exceptionHandler($e);
    }
});


\system\Config::init(AUTOLOAD_CONFIG_PATH);

define('ACTION', \system\Input::get(ACTION_KEY_NAME, DEFAULT_ACTION));

\system\Middleware::before();

if (!isset(ROUTER[ACTION])) {
    throw new \system\PageException('Page Not Found');
}
if (!isset(ROUTER[ACTION]['controller'])) {
    throw new \system\PageException('Page Not Found');
}
if (!isset(ROUTER[ACTION]['action'])) { 
    throw new \system\PageException('Page Not Found');
}

$controller = ROUTER[ACTION]['controller'];
$ins = new $controller();
if (!method_exists($ins, ROUTER[ACTION]['action'])) {
    throw new \system\PageException('Page Not Found');
}

$act = ROUTER[ACTION]['action'];
$ins->$act();

\system\Middleware::after();

//echo '<pre>',var_dump(get_included_files()),'</pre>';