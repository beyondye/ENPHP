<?php

namespace system\auth;

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

    /**
     * 认证信息
     *
     * @var string
     */
    public $message;

    /**
     * 认证信息码
     *
     * @var int
     */
    public $code;

    /**
     * 验证有效性
     *
     * @return bool
     */
    abstract protected function check();

    /**
     * 创建认证信息
     *
     * @param array $data
     *
     * @return mixed
     */
    abstract protected function create(array $data = []);

    /**
     * 获取认证信息
     *
     * @param bool $assoc
     *
     * @return mixed
     */
    abstract protected function data(bool $assoc = false);

    /**
     * 获取认证数据ID
     *
     * @return mixed
     */
    abstract protected function id();


    /**
     * 清除认证
     *
     * @return mixed
     */
    abstract protected function remove();

}
