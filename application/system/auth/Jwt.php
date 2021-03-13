<?php

namespace system\auth;

class Jwt extends AbstractAuth
{
    private $_id;
    private $_data;

    /**
     * 验证jwt数据合法性
     *
     * @return bool
     */
    public function check()
    {
        $jwt = false;

        if (AUTH_JWT_MODE == 'header') {
            $header_name = 'HTTP_' . strtoupper(AUTH_NAME);
            $jwt = isset($_SERVER[$header_name]) ? $_SERVER[$header_name] : false;
        } else if (AUTH_JWT_MODE == 'url') {
            $jwt = isset($_GET[AUTH_NAME]) ? $_GET[AUTH_NAME] : false;
        }


        if ($jwt == false) {
            $this->code = self::ERR_DATA_NULL;
            $this->message = self::MSG[self::ERR_DATA_NULL];
            return false;
        }

        $jwt_arr = explode('.', $jwt);

        if (count($jwt_arr) != 3) {
            $this->code = self::ERR_ILLEGAL;
            $this->message = self::MSG[self::ERR_ILLEGAL];
            return false;
        }

        $header = $jwt_arr[0];
        $payload = $jwt_arr[1];
        $signature = $jwt_arr[2];

        if (hash_hmac('sha256', "{$header}.{$payload}", AUTH_SECRET) == $signature) {

            $now = time();
            $payload_decode_data = json_decode($this->base64url_decode($payload));

            if (intval($payload_decode_data->exp) < $now) {
                $this->code = self::ERR_EXP;
                $this->message = self::MSG[self::ERR_EXP];
                return false;
            }

            $this->_id = $payload_decode_data->jti;
            $this->_data = $payload;

            $this->code = self::VERIFIED_SUCCESS;
            $this->message = self::MSG[self::VERIFIED_SUCCESS];

            return true;
        }

        $this->code = self::ERR_ILLEGAL;
        $this->message = self::MSG[self::ERR_ILLEGAL];

        return false;
    }

    /**
     * 获取jwt数据
     *
     * @param bool $assoc
     *
     * @return mixed|null
     */
    public function data(bool $assoc = false)
    {
        if (!isset($this->_data)) {
            return null;
        }

        $decode = $this->base64url_decode($this->_data);

        if ($assoc) {
            $data = json_decode($decode, $assoc);
            return $data['data'];
        }

        $data = json_decode($decode);
        return $data->data;
    }

    /**
     * 获取唯一jwt id
     *
     * @return string
     */
    public function id()
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
    public function create(array $data = [])
    {
        $header = $this->base64url_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));

        $this->_id = $jwt_id = uniqid('', true);

        $expire = time() + AUTH_JWT_EXPIRE;
        $payload = $this->base64url_encode(json_encode(['jti' => $jwt_id, 'exp' => $expire, 'data' => $data]));

        $this->_data = $payload;

        $signature = hash_hmac('sha256', "{$header}.{$payload}", AUTH_SECRET);

        return "$header.$payload.$signature";

    }

    /**
     * 清除认证
     *
     * @return bool
     */
    public function remove()
    {
        return true;
    }


    /**
     * 编码
     *
     * @param $data
     *
     * @return string
     */
    private function base64url_encode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * 解码
     *
     * @param $data
     *
     * @return bool|string
     */
    private function base64url_decode($data)
    {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }
}