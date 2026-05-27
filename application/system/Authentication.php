<?php

declare(strict_types=1);

namespace System;

use System\Authentication\AuthenticationException;

class Authentication
{
     /**
     * 存储单例实例
     */
    private static ?object $ins = null;

    /**
     * 返回认证实列
     *
     * @return object
     */
    public static function instance($type = 'authentication.session'): object
    {
        if (self::$ins) {
            return self::$ins;
        }

        $config = Config::get($type);

        if ($config === null) {
            throw new AuthenticationException(lang('system.authentication.type_null_config', ['type' => $type]));
        }

        if (!is_array($config)) {
            throw new AuthenticationException(lang('system.authentication.type_config_format_error', ['type' => $type]));
        }

        switch ($type) {
            case 'authentication.jwt':
                self::$ins = new authentication\Jwt($config);
                break;
            case 'authentication.cookie':
                self::$ins = new authentication\Cookie($config);
                break;
            case 'authentication.session':
                self::$ins = new authentication\Session($config);
                break;
            default:
                throw new AuthenticationException(lang('system.authentication.type_error', ['type' => $type]));
        }

        return self::$ins;
    }

    /**
     * 重置单例实例
     */
    public static function reset(): void
    {
        self::$ins = null;
    }
}
