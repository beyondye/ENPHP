<?php
return [
    // 404错误放前面，避免被通用异常捕获
    \system\PageException::class => ['http' => 404, 'biz' => 4040, 'template' => 'error/404', 'log' => true],

    // 具体的业务/模型异常放前面
    \system\authentication\AuthenticationException::class => ['http' => 500, 'biz' => 5000, 'template' => 'error/500', 'log' => true],
    \system\model\ModelException::class      => ['http' => 500, 'biz' => 5000, 'template' => 'error/500', 'log' => true],
    \system\database\DatabaseException::class => ['http' => 500, 'biz' => 5000, 'template' => 'error/500', 'log' => true],
    \system\SysException::class              => ['http' => 500, 'biz' => 5000, 'template' => 'error/500', 'log' => true],

    // 通用异常放最后面
    \ErrorException::class                   => ['http' => 500, 'biz' => 5000, 'template' => 'error/500', 'log' => true],
    \Exception::class                        => ['http' => 500, 'biz' => 5000, 'template' => 'error/500', 'log' => true],
    \Throwable::class                        => ['http' => 500, 'biz' => 5000, 'template' => 'error/500', 'log' => true],
];
