<?php

namespace system\auth;

class Cookie extends AbstractAuth
{

    private $_id;
    private $_data;

    /**
     * 验证数据合法性
     *
     * @return bool
     */
    public function check()
    {
        $cookie = isset($_COOKIE[AUTH_NAME]) ? $_COOKIE[AUTH_NAME] : false;

        if ($cookie == false) {
            $this->code = self::ERR_DATA_NULL;
            $this->message = self::MSG[self::ERR_DATA_NULL];
            return false;
        }

        $arr = explode('.', $cookie);

        if (count($arr) != 2) {
            $this->code = self::ERR_ILLEGAL;
            $this->message = self::MSG[self::ERR_ILLEGAL];
            return false;
        }

        $payload = $arr[0];
        $signature = $arr[1];

        if (hash_hmac('sha256', $payload, AUTH_SECRET) == $signature) {

            $payload_decode_data = json_decode(base64_decode($payload));

            $this->_id = $payload_decode_data->id;
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
     * 获取数据
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

        $decode = base64_decode($this->_data);

        if ($assoc) {
            $data = json_decode($decode, $assoc);
            return $data['data'];
        }

        $data = json_decode($decode);
        return $data->data;
    }

    /**
     * 获取唯一id
     *
     * @return string
     */
    public function id()
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
    public function create(array $data = [])
    {
        $this->_id = $cookie_id = uniqid('', true);

        if (AUTH_COOKIE_EXPIRE > 0) {
            $expire = time() + AUTH_COOKIE_EXPIRE;
        } else {
            $expire = 0;
        }

        $payload = base64_encode(json_encode(['id' => $cookie_id, 'exp' => $expire, 'data' => $data]));
        $this->_data = $payload;
        $signature = hash_hmac('sha256', $payload, AUTH_SECRET);
        $value = "$payload.$signature";

        setcookie(AUTH_NAME, $value, $expire, COOKIE_PATH, COOKIE_DOMAIN, COOKIE_SECURE, COOKIE_HTTPONLY);

        return $value;

    }

    /**
     * 清除认证
     *
     * @return bool
     */
    public function remove()
    {
        return setcookie(AUTH_NAME, '', 1, COOKIE_PATH, COOKIE_DOMAIN, COOKIE_SECURE, COOKIE_HTTPONLY);

    }

}