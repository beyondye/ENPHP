<?php
//route
define('DEFAULT_CONTROLLER', 'main');
define('DEFAULT_ACTION', 'index');
define('MODULE_KEY_NAME', 'm');
define('CONTROLLER_KEY_NAME', 'c');
define('ACTION_KEY_NAME', 'a');
define('EXT', '.php');

//output编码
define('CHARSET', 'utf-8');

//cookie
define('COOKIE_DOMAIN', '');
define('COOKIE_SECURE', false);
define('COOKIE_PATH', '/');
define('COOKIE_HTTPONLY', false);
define('COOKIE_EXPIRE', 0);

//session
define('SESSION_COOKIE_NAME', 'SE');
define('SESSION_EXPIRE', 0);

//security
define('ENCRYPTION_KEY', 'weryi9878sdftgtbsdfh');
define('TOKEN_SESSION_NAME', '34efdre');
define('TOKEN_INPUT_NAME', 'fh40dfk98dkfje');
define('TOKEN_EXPIRE', 3600);

//认证方式，cookie,jwt,session
if (!defined('AUTH_TYPE')) {
    define('AUTH_TYPE', 'session');
}
define('AUTH_SECRET', 'dsd#@4ddz!ds'); //加密密钥
define('AUTH_NAME', 'auth');            //认证名称
define('AUTH_JWT_EXPIRE', 600);        //jwt存活时间,秒为单位
define('AUTH_JWT_MODE', 'header');     //jwt数据过载方式，header或url
define('AUTH_COOKIE_EXPIRE', 0);       //认证cookie存活时间


//默认语言环境
define('LANG', 'zh_cn');

//url 重写
define('URL', ['mod_name' => ['controller_name/action_name' => '/{controller_key}/{action_key}']]);

//profiler log file path
define('PROFILER_LOG_FILE', APP_DIR . 'log/profiler.log');