<?php

declare(strict_types=1);

namespace System\Authentication;

class Session extends AbstractAuthentication
{
    // 唯一id
    private string $_id;

    // 认证数据
    private string $_data;

    // 配置
    private array $_config;

    public function __construct(array $config)
    {
        $this->_config = $config;
    }
    /**
     * 验证数据合法性
     *
     * @return bool
     */
    public function check(): bool
    {
        $session = \system\Session::get($this->_config['name']);
        if ($session == null) {
            throw new AuthenticationException(lang('system.authentication.session_null'));
        }

        $decode_data = json_decode($session);

        if ($decode_data == null) {
            throw new AuthenticationException(lang('system.authentication.session_illegal'));
        }

        if (!isset($decode_data->status)) {
            throw new AuthenticationException(lang('system.authentication.session_illegal'));
        }

        if (!isset($decode_data->data)) {
            throw new AuthenticationException(lang('system.authentication.session_illegal'));
        }

        if ($decode_data->status == 'ok') {
            $this->_data = $session;
            $this->_id = session_id();
            return true;
        }

        throw new AuthenticationException(lang('system.authentication.session_illegal'));
    }

    /**
     * 获取数据
     *
     * @param bool $assoc
     *
     * @return array|object|null
     */
    public function data(bool $assoc = false): array|object|null
    {
        if (!isset($this->_data)) {
            return null;
        }

        if ($assoc) {
            $data = json_decode($this->_data, $assoc);
            return $data['data'] ?? null;
        }

        $data = json_decode($this->_data);
        return $data->data ?? null;
    }

    /**
     * 获取唯一id
     *
     * @return string
     */
    public function id(): string
    {
        return $this->_id;
    }


    /**
     * 创建认证数据
     *
     * @param array $data
     *
     * @return string
     */
    public function create(array $data = []): string
    {

        $payload = json_encode(['status' => 'ok', 'data' => $data]);
        \system\Session::set($this->_config['name'], $payload);
        $this->_data = $payload;
        $this->_id = session_id();
        return $this->_id;
    }

    /**
     * 清除认证
     *
     * @return bool
     */
    public function remove(): bool
    {
        \system\Session::delete($this->_config['name']);
        return true;
    }
}
