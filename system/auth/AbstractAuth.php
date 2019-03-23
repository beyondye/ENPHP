<?php

namespace system\auth;

/**
 * auth 抽象类
 * @author Ding<beyondye@gmail.com>
 */
abstract class AbstractAuth
{
    const ERR_ILLEGAL = 1;
    const ERR_EXP = 2;
    const ERR_DATA_NULL = 3;
    const VERIFIED_SUCCESS = 0;
    const MSG = [
        self::ERR_EXP => '认证过期',
        self::ERR_ILLEGAL => '非法认证',
        self::ERR_DATA_NULL => '无认证数据',
        self::VERIFIED_SUCCESS => '认证通过'
    ];

    public $message;
    public $code;

    //验证有效性
    abstract protected function verify();

    //创建认证信息
    abstract protected function create(array $data = []);

    //获取认证信息
    abstract protected function data($assoc = false);

    //获取认证数据ID
    abstract protected function id();

    //清除认证
    abstract protected function remove();

}
