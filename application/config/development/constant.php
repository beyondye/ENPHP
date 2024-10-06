<?php
//route
const DEFAULT_CONTROLLER = 'main';
const DEFAULT_ACTION = 'index';
const MODULE_KEY_NAME = 'm';
const CONTROLLER_KEY_NAME = 'c';
const ACTION_KEY_NAME = 'a';
const EXT = '.php';

//output编码
const CHARSET = 'utf-8';
//cookie域
const COOKIE_DOMAIN = '';
//cookie是否https连接
const COOKIE_SECURE = false;
//cookie有效目录
const COOKIE_PATH = '/';
//cookie http读取
const COOKIE_HTTPONLY = true;
//cookie过期时间
const COOKIE_EXPIRE = 0;

//session名称
const SESSION_COOKIE_NAME = 'SE';
//session过期时间
const SESSION_EXPIRE = 0;

//安全key
const ENCRYPTION_KEY = 'weryi9878sdftgtbsdfh';

//表单认证session名
const TOKEN_SESSION_NAME = '34efdre';
//表单项认证名
const TOKEN_INPUT_NAME = 'fh40dfk98dkfje';
//表单项认证过期时间
const TOKEN_EXPIRE = 3600;

//认证方式，cookie,jwt,session
const AUTH_TYPE = 'session';
//加密密钥
const AUTH_SECRET = 'dsd#@4ddz!ds';
//认证名称
const AUTH_NAME = 'auth';
//jwt存活时间,秒为单位
const AUTH_JWT_EXPIRE = 600;
//jwt数据过载方式，header或url
const AUTH_JWT_MODE = 'header';
//认证cookie存活时间
const AUTH_COOKIE_EXPIRE = 0;

//默认语言环境
const LANG = 'zh';
//语言数据目录
const LANG_DIR = APP_DIR . 'locale/lang/';

//URL重写
const URL = [
    'www/news/category' => [
        '/' => '/news.html',
        '/page' => '/news_{page}.html',
        '/letter' => '/news_{letter}.html',
        '/letter/page' => '/news_{letter}_{page}.html',
    ],
    'www/news/detail' => [
        '/id' => '/news/{id}.html'
    ]
];

//分析器日志文件
const PROFILER_LOG_FILE = APP_DIR . 'log/profiler.log';

//中间件设置
const MIDDLEWARE = [
    'before' => [
        'auth' => \middleware\Auth::class,
        'authorize' => \middleware\Authorize::class,
        'lang' => \middleware\Lang::class
    ],
    'after' => []
];
