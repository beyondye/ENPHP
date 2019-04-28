<?php

namespace system\auth;

/**
 * session认证
 *
 * @author Ding<beyondye@gmail.com>
 */
class Session extends AbstractAuth
{

    private $_data;

    //验证数据合法性
    public function verify()
    {
        global $sys;

        $session = $sys->session->get(AUTH_NAME);

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

    //获取数据
    public function data($assoc = false)
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

    //获取唯一id
    public function id()
    {
        return session_id();
    }


    //创建认证数据
    public function create(array $data = [])
    {

        $payload = json_encode(['status' => 'ok', 'data' => $data]);

        global $sys;
        $sys->session->set(AUTH_NAME, $payload);

        $this->_data = $payload;

        return '';

    }

    //清除认证
    public function remove()
    {
        global $sys;
        $sys->session->delete(AUTH_NAME);
        return true;
    }

}