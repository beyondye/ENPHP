<?php
return [
    // 404错误放前面，避免被通用异常捕获
    \System\PageException::class => ['http' => 404, 'biz' => 4040, 'template' => 'errors/404', 'log' => true],

    // 具体的业务/模型异常放前面
    \System\Authentication\AuthenticationException::class => ['http' => 500, 'biz' => 5000, 'template' => 'error/500', 'log' => true],
    \System\Model\ModelException::class      => ['http' => 500, 'biz' => 5000, 'template' => 'errors/500', 'log' => true],
    \System\Database\DatabaseException::class => ['http' => 500, 'biz' => 5000, 'template' => 'errors/500', 'log' => true],
    \System\SysException::class              => ['http' => 500, 'biz' => 5000, 'template' => 'errors/500', 'log' => true],

    // 通用异常放最后面
    \ErrorException::class                   => ['http' => 500, 'biz' => 5000, 'template' => 'errors/500', 'log' => true],
    \Exception::class                        => ['http' => 500, 'biz' => 5000, 'template' => 'errors/500', 'log' => true],
    \Throwable::class                        => ['http' => 500, 'biz' => 5000, 'template' => 'errors/500', 'log' => true],
];
  