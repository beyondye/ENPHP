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
define('SESSION_USE_DATABASE', false);
define('SESSION_DATABASE_NAME', 'session');
define('SESSION_TABLE_NAME', 'sessions');

//security
define('ENCRYPTION_KEY', 'weryi9878sdftgtbsdfh');
define('TOKEN_SESSION_NAME', '34efdre');
define('TOKEN_INPUT_NAME', 'fh40dfk98dkfje');
define('USE_TOKEN', false);


//默认语言环境
define('LANG', 'zh_cn');

//url 重写
define('URL', ['mod_name'=>['controller_name/action_name'=>'/{controller_key}/{action_key}']]);


