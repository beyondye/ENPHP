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
        //session_regenerate_id();
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
     * 闪取会话
     * 
     * @param string $name
     * @return string|null;
     */
    public function flash($name)
    {
        $val = $this->get($name);
        if ($val == null) {
            return null;
        }

        unset($_SESSION[$name]);

        return $val;
    }

    /**
     * 删除一个会话
     * @param void
     */
    public function delete($name)
    {
        unset($_SESSION[$name]);
    }

    /**
     * 重新生成会话id
     * @return bool
     */
    public function regenerate()
    {
        return session_regenerate_id();
    }

    /**
     * 销毁全部会话数据
     * @return bool
     */
    public function destroy()
    {
        setcookie(SESSION_COOKIE_NAME, '', 1, COOKIE_PATH, COOKIE_DOMAIN, COOKIE_SECURE, COOKIE_HTTPONLY);
        return session_destroy();
    }

}
