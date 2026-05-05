<?php

declare(strict_types=1);

namespace system;

class Input
{
    public static function get(string $name = '', $default = null): mixed
    {
        if ($name === '') {
            return $_GET;
        }

        if (!isset($_GET[$name])) {
            return $default;
        }

        $value = $_GET[$name];

        if (is_string($value) && trim($value) === '') {
            return $default;
        }

        return $value;
    }

    public static function post(string $name = '', $default = null): mixed
    {
        $func = function ($val) {
            if (is_array($val)) {
                return array_map(function ($v) {
                    return trim($v);
                }, $val);
            }
            return trim($val);
        };

        if ($name === '') {
            return array_map($func, $_POST);
        }

        if (!isset($_POST[$name])) {
            return $default;
        }

        return $func($_POST[$name]);
    }

    public static function method(): string
    {
        return $_SERVER['REQUEST_METHOD'] ?? '';
    }

    public static function referer(): string
    {
        return $_SERVER['HTTP_REFERER'] ?? '';
    }

    public static function body(): string
    {
        return file_get_contents("php://input");
    }

    public static function isAjax(): bool
    {
        // 1. 处理 X-Requested-With (兼容 jQuery, Axios 等库)
        $requestedWith = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';
        if (strtolower($requestedWith) === 'xmlhttprequest') {
            return true;
        }

        // 2. 兼容现代 Fetch API 及某些 REST 客户端
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        if (str_contains(strtolower($accept), 'application/json')) {
            return true;
        }

        return false;
    }

    public static function ip(): string
    {
        $keys = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];

        foreach ($keys as $key) {
            $ip = getenv($key) ?: ($_SERVER[$key] ?? '');
            if ($ip) {
                // 处理 X-Forwarded-For 可能返回的多个IP（取第一个）
                if (str_contains($ip, ',')) {
                    $ip = explode(',', $ip)[0];
                }
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return '0.0.0.0';
    }

    public static function host(): string
    {
        return $_SERVER['HTTP_HOST'] ?? '';
    }
}
