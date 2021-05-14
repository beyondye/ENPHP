<?php
//错误显示设置
error_reporting(E_ALL);

//设置时区
date_default_timezone_set('PRC');

//设置运行环境
// test,production,development
define('ENVIRONMENT', 'development');

//开启运行分析
define('PROFILER', true);

//设置controller模块
define('MODULE', 'www');

//设置模板
define('TEMPLATE', 'www');

//应用程序目录
define('APP_DIR', realpath('../../') . DIRECTORY_SEPARATOR);

//系统文件目录
define('SYS_DIR', realpath('../../system') . DIRECTORY_SEPARATOR);

//入口地址
define('ENTRY', $_SERVER['PHP_SELF']);

//常量配置
define('CONST_FILE', '');

//初始脚本
require_once SYS_DIR . 'init.php';