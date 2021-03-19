<?php

namespace system\auth;

class Session extends AbstractAuth
{
    private $_data;

    /**
     * 验证数据合法性
     *
     * @return bool
     */
    public function check()
    {
        $session = \system\Session::get(AUTH_NAME);
        if ($session == null) {
            $this->code = self::ERR_DATA_NULL;
            $this->message = self::MSG[self::ERR_DATA_NULL];
            return false;
        }

        $decode_data = json_decode($session);
        if ($decode_data->status == 'ok') {
            $this->_data = $session;
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

        if ($assoc) {
            $data = json_decode($this->_data, $assoc);
            return $data['data'];
        }

        $data = json_decode($this->_data);
        return $data->data;
    }

    /**
     * 获取唯一id
     *
     * @return string
     */
    public function id()
    {
        return session_id();
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
        $payload = json_encode(['status' => 'ok', 'data' => $data]);
        \system\Session::set(AUTH_NAME, $payload);
        $this->_data = $payload;

        return '';
    }

    /**
     * 清除认证
     *
     * @return bool
     */
    public function remove()
    {
        \system\Session::delete(AUTH_NAME);
        return true;
    }

}