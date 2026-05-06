<?php
//route
const DEFAULT_ACTION = 'main/index';
const ACTION_KEY_NAME = 'act';
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


//语言环境列表
const LANG_LIST = [
    'zh' => '中文',
    'en' => 'English',
    'es' => 'Español',
    'pt' => 'Português',
    'ja' => '日本語',
    'de' => 'Deutsch',
    'fr' => 'Français',
    'it' => 'Italiano',
    'ru' => 'Русский',
    'ko' => '한국어',
    'tr' => 'Türkçe',
    'nl' => 'Nederlands',
    'pl' => 'Polski'
];
//默认语言
const LANG = 'zh';
//自定义语言数据目录
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
    ],
    'test' => [
        '/' => '/test',
        '/id' => '/test/{id}',
        '/id/name' => '/test/{id}/{name}'
    ]
];

//分析器日志文件
const PROFILER_LOG_FILE = APP_DIR . 'log/profiler.log';

//中间件设置
const MIDDLEWARE = [
    'before' => [
        //'auth' => middleware\Auth::class,
        //'authorize' => middleware\Authorize::class,
        //'lang' => middleware\Lang::class
    ],
    'after' => []
];

//异常映射表
const EXCEPTION_MAP = [

    // 404错误放前面，避免被通用异常捕获
    \system\PageException::class => ['http' => 404, 'biz' => 4040, 'template' => 'error/404', 'log' => true],

    // 具体的业务/模型异常放前面
    \system\model\ModelException::class      => ['http' => 500, 'biz' => 5000, 'template' => 'error/500', 'log' => true],
    \system\database\DatabaseException::class => ['http' => 500, 'biz' => 5000, 'template' => 'error/500', 'log' => true],
    \system\SysException::class              => ['http' => 500, 'biz' => 5000, 'template' => 'error/500', 'log' => true],

    // 通用异常放最后面
    \ErrorException::class                   => ['http' => 500, 'biz' => 5000, 'template' => 'error/500', 'log' => true],
    \Exception::class                        => ['http' => 500, 'biz' => 5000, 'template' => 'error/500', 'log' => true],
    \Throwable::class                        => ['http' => 500, 'biz' => 5000, 'template' => 'error/500', 'log' => true],

];
//异常日志文件
const LOG_FILE = APP_DIR . 'log/error.log';

//路由配置
const ROUTER = [
    'main/index' => [
        'controller' => \app\module\www\Main::class,
        'action' => 'index'
    ],
];

//自动加载配置文件路径
const AUTOLOAD_CONFIG_PATH = APP_DIR . 'config/' . ENVIRONMENT . '/autoload/';

//类映射
const  CLASS_MAP= [
    'system' => SYS_DIR,
    'app' => APP_DIR,
];