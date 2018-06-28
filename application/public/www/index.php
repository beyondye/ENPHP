<?php

//错误显示设置
error_reporting(E_ALL);

//设置时区
date_default_timezone_set('PRC');

//设置运行环境
// test,production,development
define('ENVIRONMENT', 'development');

//设置controller模块
define('MODULE', 'www');

//设置模板
define('TEMPLATE', 'www');

//应用程序目录
define('APP_DIR', realpath('../../') . DIRECTORY_SEPARATOR);

//系统文件目录
define('SYS_DIR', realpath('../../framework/system') . DIRECTORY_SEPARATOR);

require_once SYS_DIR . 'init.php';
