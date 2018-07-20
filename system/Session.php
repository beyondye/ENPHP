<?php

namespace system;

/**
 * 会话类
 * 
 * @author Ding <beyondye@gmail.com>
 */
class Session
{

    public function __construct()
    {
        session_name(SESSION_COOKIE_NAME);
        session_set_cookie_params(SESSION_EXPIRE, COOKIE_PATH, COOKIE_DOMAIN, COOKIE_SECURE, COOKIE_HTTPONLY);
        session_start();
        session_regenerate_id();
    }

    /**
     * 设置会话
     * 
     * @param string $name
     * @param string $value
     * 
     * @return boolean
     */
    public function set($name, $value = '')
    {
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
    public function get($name)
    {
        return isset($_SESSION[$name]) ? $_SESSION[$name] : null;
    }

    /**
     * 销毁数据
     * @return bool
     */
    public function destroy()
    {
        $this->set(SESSION_COOKIE_NAME, '');
        return session_destroy();
    }

}
