<?php

declare(strict_types=1);

namespace system\authentication;

use system\authentication\AbstractAuthentication;

class Jwt extends AbstractAuthentication
{
    private string $_id;

    private string $_data;

    private array $_config;

    public function __construct(array $config)
    {
        $this->_config = $config;       
    }

    /**
     * 验证jwt数据合法性
     *
     * @return bool
     */
    public function check(): bool
    {
        $jwt = false;
        if ($this->_config['mode'] == 'header') {
            $header_name = 'HTTP_' . strtoupper($this->_config['name']);
            $jwt = $_SERVER[$header_name] ?? false;
        } else if ($this->_config['mode'] == 'url') {
            $jwt = $_GET[$this->_config['name']] ?? false;
        }

        if ($jwt == false) {
            throw new AuthenticationException(lang('system.authentication.jwt_null'));
        }

        $jwt_arr = explode('.', $jwt);
        if (count($jwt_arr) != 3) {
            throw new AuthenticationException(lang('system.authentication.jwt_illegal'));
        }

        $header = $jwt_arr[0];
        $payload = $jwt_arr[1];
        $signature = $jwt_arr[2];

        $signature_verify = $this->base64url_encode(hash_hmac('sha256', "{$header}.{$payload}", $this->_config['secret'], true));

      

        if (hash_equals($signature_verify, $signature)) {

            $now = time();
            $payload_decode_data = json_decode($this->base64url_decode($payload));

            if (!$payload_decode_data || !isset($payload_decode_data->exp) || !isset($payload_decode_data->jti)) {
                throw new AuthenticationException(lang('system.authentication.jwt_illegal'));
            }

            if (intval($payload_decode_data->exp) < $now) {
                throw new AuthenticationException(lang('system.authentication.jwt_exp'));
            }

            $this->_id = $payload_decode_data->jti;
            $this->_data = $payload;

            return true;
        }

        throw new AuthenticationException(lang('system.authentication.jwt_verify_failed'));
    }

    /**
     * 获取jwt数据
     *
     * @param bool $assoc
     *
     * @return array|null|object
     */
    public function data(bool $assoc = false): array|null|object
    {
        if (!isset($this->_data)) {
            return null;
        }

        $decode = $this->base64url_decode($this->_data);
        if ($assoc) {
            $data = json_decode($decode, $assoc);
            return $data['data'] ?? null;
        }

        $data = json_decode($decode);
        return $data->data ?? null;
    }

    /**
     * 获取唯一jwt id
     *
     * @return string
     */
    public function id(): string
    {
        return $this->_id;
    }


    /**
     * 创建jwt认证数据
     *
     * @param array $data
     *
     * @return string
     */
    public function create(array $data = []): string
    {
        $header = $this->base64url_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));

        $this->_id = $jwt_id = uniqid('', true);
        $expire = time() + $this->_config['expires'];

        $payload = $this->base64url_encode(json_encode(['jti' => $jwt_id, 'exp' => $expire, 'data' => $data]));
        $this->_data = $payload;

        $signature = hash_hmac('sha256', "{$header}.{$payload}", $this->_config['secret'], true);
        $signature = $this->base64url_encode($signature);

        return "$header.$payload.$signature";
    }

    /**
     * 清除认证
     *
     * @return bool
     */
    public function remove(): bool
    {
        return true;
    }


    /**
     * 编码
     *
     * @param string $data
     *
     * @return string
     */
    private function base64url_encode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * 解码
     *
     * @param string $data
     *
     * @return bool|string
     */
    private function base64url_decode(string $data): string|false
    {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }
}
