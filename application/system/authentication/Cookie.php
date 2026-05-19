<?php

declare(strict_types=1);

namespace system\authentication;

use system\authentication\AuthenticationException;

class Cookie extends AbstractAuthentication
{
    //认证唯一id
    private string $_id;

    //认证数据
    private string $_data;

    //认证配置
    private array $_config;

    /**
     * 构造函数
     *
     * @param array $config
     */
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
        $cookie = $_COOKIE[$this->_config['name']] ?? false;
        if ($cookie == false) {
            throw new AuthenticationException(lang('system.authentication.cookie_null'));
        }

        $arr = explode('.', $cookie);
        if (count($arr) != 2) {
            throw new AuthenticationException(lang('system.authentication.cookie_format_error'));
        }

        $payload = $arr[0];
        $signature = $arr[1];

        $signature_verify = hash_hmac('sha256', $payload, $this->_config['secret']);

        if (hash_equals($signature_verify, $signature)) {

            $payload_decode_data = json_decode(base64_decode($payload));

            if ($payload_decode_data === null) {
                throw new AuthenticationException(lang('system.authentication.cookie_decode_error'));
            }

            if (intval($payload_decode_data->exp) < time() && intval($this->_config['expires']) > 0) {
                throw new AuthenticationException(lang('system.authentication.cookie_expire'));
            }

            $this->_id = $payload_decode_data->id;
            $this->_data = $payload;
            return true;
        }

        throw new AuthenticationException(lang('system.authentication.cookie_signature_error'));
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

        $decode = base64_decode($this->_data);
        if ($assoc) {
            $data = json_decode($decode, $assoc);
            return $data['data'] ?? null;
        }

        $data = json_decode($decode);
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
        $this->_id = $cookie_id = uniqid('', true);

        if ($this->_config['expires'] > 0) {
            $expire = time() + $this->_config['expires'];
        } else {
            $expire = 0;
        }

        $payload = base64_encode(json_encode(['id' => $cookie_id, 'exp' => $expire, 'data' => $data]));
        $this->_data = $payload;
        $signature = hash_hmac('sha256', $payload, $this->_config['secret']);
        $value = "$payload.$signature";

        $options = array_merge(COOKIE_OPTIONS, ['expires' => $expire]);
        setcookie($this->_config['name'], $value, $options);

        return $value;
    }

    /**
     * 清除认证
     *
     * @return bool
     */
    public function remove(): bool
    {
        $options = array_merge(COOKIE_OPTIONS, ['expires' => 1]);
        return setcookie($this->_config['name'], '', $options);
    }
}
