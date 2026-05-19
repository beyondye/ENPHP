<?php

declare(strict_types=1);

namespace system;

class Session
{
    /**
     * 开启会话
     */
    public static function start(): void
    {
        static $start = false;
        if ($start === false) {
            session_name(SESSION_COOKIE_NAME);
            session_set_cookie_params(SESSION_COOKIE_OPTIONS);
            session_start();
            $start = true;
        }
    }


    /**
     * 设置会话数据
     *
     * @param string $name
     * @param mixed $value
     *
     * @return bool
     */
    public static function set(string $name, mixed $value = ''): bool
    {
        self::start();

        if ($value !== '' && $value !== null) {
            $_SESSION[$name] = $value;
        } else {
            unset($_SESSION[$name]);
        }

        return true;
    }

    /**
     * 获取会话
     *
     * @param string $name
     *
     * @return mixed
     */
    public static function get(string $name): mixed
    {
        self::start();
        return $_SESSION[$name] ?? null;
    }

    /**
     * 闪取会话
     *
     * @param string $name
     *
     * @return mixed
     */
    public static function flash(string $name): mixed
    {
        $val = self::get($name);
        if ($val === null) {
            return null;
        }

        unset($_SESSION[$name]);

        return $val;
    }

    /**
     * 删除一个会话
     *
     * @param array|string $name
     *
     * @return void
     */
    public static function delete($name): void
    {
        self::start();

        if (is_array($name)) {
            foreach ($name as $k => $v) {
                self::delete($v);
            }
            return;
        }

        unset($_SESSION[$name]);
    }

    /**
     * 重新生成会话id
     *
     * @return bool
     */
    public static function regenerate(bool $destroy = false): bool
    {
        self::start();
        return session_regenerate_id($destroy);
    }

    /**
     * 销毁全部会话数据
     *
     * @return bool
     */
    public static function destroy(): bool
    {
        self::start();
        $_SESSION = [];
        $options = array_merge(COOKIE_OPTIONS, ['expires' => 1]);
        setcookie(SESSION_COOKIE_NAME, '', $options);
        return session_destroy();
    }
}
