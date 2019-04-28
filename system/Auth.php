<?php

namespace system;

/**
 * 用户认证
 *
 * @author Ding<beyondye@gmail.com>
 *
 */
class Auth
{

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