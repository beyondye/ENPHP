<?php

namespace system;

class Security
{
    /**
     * 生成表单token，同一个上下文只能有一个token
     *
     * @global object array $instances
     * @return string
     */
    public function token()
    {
        $time = time();
        $hash = hash_hmac('sha1', $time, ENCRYPTION_KEY);
        $token = base64_encode($time . '|' . $hash);

        Session::set(TOKEN_SESSION_NAME, $hash);

        return $token;
    }

    /**
     * token input 名称
     *
     * @global object $sys
     * @return string
     */
    public function tokenName()
    {
        $name = uniqid('_');
        Session::set(TOKEN_INPUT_NAME, $name);

        return $name;
    }

    /**
     * 验证token
     *
     * @global object $sys
     * @return boolean
     */
    function checkToken()
    {
        $name = Session::get(TOKEN_INPUT_NAME);
        if (!$name) {
            return false;
        }

        $clienttoken = Input::post($name);
        if (!$clienttoken) {
            return false;
        }

        $clienttoken = explode('|', base64_decode($clienttoken));

        if (count($clienttoken) != 2) {
            return false;
        }

        $clienttime = $clienttoken[0];
        $clienthash = $clienttoken[1];

        if ((intval($clienttime) + TOKEN_EXPIRE) < time()) {
            return false;
        }

        $serverhash = Session::get(TOKEN_SESSION_NAME);
        if ($serverhash == hash_hmac('sha1', $clienttime, ENCRYPTION_KEY)) {
            Session::delete(TOKEN_INPUT_NAME);
            Session::delete(TOKEN_SESSION_NAME);
            Session::regenerate();
            return true;
        }

        Session::delete(TOKEN_INPUT_NAME);
        Session::delete(TOKEN_SESSION_NAME);
        Session::regenerate();

        return false;
    }

}
