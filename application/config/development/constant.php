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

//cookie域
define('COOKIE_DOMAIN', '');

//cookie是否https连接
define('COOKIE_SECURE', false);

//cookie有效目录
define('COOKIE_PATH', '/');

//cookie http读取
define('COOKIE_HTTPONLY', true);

//cookie过期时间
define('COOKIE_EXPIRE', 0);


//session名称
define('SESSION_COOKIE_NAME', 'SE');

//session过期时间
define('SESSION_EXPIRE', 0);


//安全key
define('ENCRYPTION_KEY', 'weryi9878sdftgtbsdfh');


//表单认证session名
define('TOKEN_SESSION_NAME', '34efdre');

//表单项认证名
define('TOKEN_INPUT_NAME', 'fh40dfk98dkfje');

//表单项认证过期时间
define('TOKEN_EXPIRE', 3600);


//认证方式，cookie,jwt,session
define('AUTH_TYPE', 'session');

//加密密钥
define('AUTH_SECRET', 'dsd#@4ddz!ds');

//认证名称
define('AUTH_NAME', 'auth');

//jwt存活时间,秒为单位
define('AUTH_JWT_EXPIRE', 600);

//jwt数据过载方式，header或url
define('AUTH_JWT_MODE', 'header');

//认证cookie存活时间
define('AUTH_COOKIE_EXPIRE', 0);


//默认语言环境
define('LANG', 'zh_cn');

//URL重写
define('URL', [
    'mod_name' => [
        'controller_name/action_name' => '/{controller_key}/{action_key}'
    ]
]);

//分析器日志文件
define('PROFILER_LOG_FILE', APP_DIR . 'log/profiler.log');

//中间件设置
define('MIDDLEWARE', [
    'before' => [
        'auth' => \middleware\Auth::class,
        'authorize' => \middleware\Authorize::class
    ],
    'after' => []
]);
