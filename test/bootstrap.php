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

//应用程序目录
define('APP_DIR', realpath('./application') . DIRECTORY_SEPARATOR);

//系统文件目录
define('SYS_DIR', realpath('./application/system') . DIRECTORY_SEPARATOR);

//设置模块目录
const MODULE_DIR = APP_DIR . 'module/www/';

//设置模板目录
const TEMPLATE_DIR = APP_DIR . 'template/www/';

//入口地址
define('ENTRY', $_SERVER['PHP_SELF']);

//配置目录
const CONFIG_DIR = APP_DIR . 'config/' . ENVIRONMENT . '/';

//常量配置文件
const CONST_FILE = CONFIG_DIR . 'constant.php';


//include constant file
include CONST_FILE;
include SYS_DIR . 'func.php';

require_once './vendor/autoload.php';

\system\Config::init(AUTOLOAD_CONFIG_PATH);

