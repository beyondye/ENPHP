<?php

namespace system;


class Auth
{
    /**
     * 返回认证实列
     *
     * @return object
     */
    public static function instance()
    {
        static $ins = null;
        if ($ins) {
            return $ins;
        }

        if (AUTH_TYPE == 'jwt') {
            $ins = new auth\Jwt();
        } else if (AUTH_TYPE == 'cookie') {
            $ins = new auth\Cookie();
        } else if (AUTH_TYPE == 'session') {
            $ins = new auth\Session();
        } else {
            $ins = null;
        }

        return $ins;
    }

}