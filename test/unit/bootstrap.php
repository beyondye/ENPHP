<?php

//错误显示设置
error_reporting(E_ALL);

//设置时区
date_default_timezone_set('PRC');

//设置运行环境
// test,production,development
const ENVIRONMENT = 'development';

//开启运行分析
const PROFILER = false;

//设置controller模块
const MODULE = 'www';

//设置模板
const TEMPLATE = 'www';

//应用程序目录
define('APP_DIR', './application/');

//系统文件目录
define('SYS_DIR', APP_DIR . 'system/');

//入口地址
define('ENTRY', $_SERVER['PHP_SELF']);

//常量配置
const CONST_FILE = 'constant';


//global variable
$vars = [];

//include constant file
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

