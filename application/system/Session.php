<?php

namespace system;

class Session
{

    /**
     * 开启会话
     */
    public static function start()
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
     * 设置会话
     *
     * @param string $name
     * @param string $value
     *
     * @return boolean
     */
    public static function set(string $name, string $value = '')
    {
        self::start();

        if ($value != '') {
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
     * @return string|null
     */
    public static function get(string $name)
    {
        self::start();
        return $_SESSION[$name] ?? null;
    }

    /**
     * 闪取会话
     *
     * @param string $name
     *
     * @return string|null;
     */
    public static function flash(string $name)
    {
        $val = self::get($name);
        if ($val == null) {
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
    public static function delete($name)
    {
        self::start();

        if (is_array($name)) {
            foreach ($name as $k => $v) {
                self::delete($v);
            }
        }

        unset($_SESSION[$name]);
    }

    /**
     * 重新生成会话id
     *
     * @return bool
     */
    public static function regenerate()
    {
        self::start();
        return session_regenerate_id();
    }

    /**
     * 销毁全部会话数据
     *
     * @return bool
     */
    public static function destroy()
    {
        self::start();
        $options = array_merge(COOKIE_OPTIONS, ['expires' => 1]);
        setcookie(SESSION_COOKIE_NAME, '', $options);
        return session_destroy();
    }
}
