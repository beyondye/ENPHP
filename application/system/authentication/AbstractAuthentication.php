<?php
declare(strict_types=1);

namespace system\authentication;

abstract class AbstractAuthentication
{
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
    abstract protected function check(): bool;

    /**
     * 创建认证数据
     *
     * @param array $data
     *
     * @return string
     */
    abstract protected function create(array $data = []): string;

    /**
     * 获取认证信息
     *
     * @param bool $assoc
     *
     * @return array|object|null
     */
    abstract protected function data(bool $assoc = false): array|object|null;

    /**
     * 获取认证数据ID
     *
     * @return string
     */
    abstract protected function id(): string;


    /**
     * 清除认证数据
     *
     * @return bool
     */
    abstract protected function remove(): bool;

}
