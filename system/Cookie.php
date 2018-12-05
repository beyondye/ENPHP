<?php

namespace system;

/**
 * cookie类
 *
 * @author Ding<beyondye@gmail.com>
 */
class Cookie
{

    /**
     * 获取cookie数据,不存在返回null，不带参数返回全部
     *
     * @param string $name
     *
     * @return array|null
     */
    public function get($name = null)
    {

        if ($name === null) {
            return $_COOKIE;
        }

        return isset($_COOKIE[$name]) ? $_COOKIE[$name] : null;
    }

    /**
     * 设置cookie输出
     *
     * @param string $name Cookie name
     * @param string $value Cookie value
     * @param int $expire 过期秒数基于当前时间戳之上，设置0为关闭浏览器失效
     * @param string $path cookie有效路径
     * @param string $domain cookie有效域名
     * @param boolean $secure 是否必须https
     * @param boolean $httponly http唯一读取cookie
     *
     * @return boolean
     */
    public function set($name, $value, $expire = COOKIE_EXPIRE, $path = COOKIE_PATH, $domain = COOKIE_DOMAIN, $secure = COOKIE_SECURE, $httponly = COOKIE_HTTPONLY)
    {
        if (!$name) {
            return false;
        }

        $expire = intval($expire) == 0 ? 0 : time() + intval($expire);
        return setcookie($name, $value, $expire, $path, $domain, $secure, $httponly);
    }

    /**
     * 设置cookie输出
     *
     * @param array $data Cookie数据格式 ['name'=>'value','name2'=>'value2']
     * @param int $expire 过期秒数基于当前时间戳之上，设置0为关闭浏览器失效
     * @param string $path 有效路径
     * @param string $domain 有效域名
     * @param boolean $secure 是否https传输
     * @param boolean $httponly http读唯一
     *
     * @return boolean
     */
    public function many($data, $expire = COOKIE_EXPIRE, $path = COOKIE_PATH, $domain = COOKIE_DOMAIN, $secure = COOKIE_SECURE, $httponly = COOKIE_HTTPONLY)
    {
        if (!is_array($data)) {
            return false;
        }

        $expire = intval($expire) == 0 ? 0 : time() + intval($expire);

        foreach ($data as $key => $val) {
            setcookie($key, $val, $expire, $path, $domain, $secure, $httponly);
        }

        return true;
    }

    /**
     * 删除一个cookie
     *
     * @param string $name
     * @return bool
     */
    public function delete($name)
    {
        if (is_array($name)) {
            foreach ($name as $k => $v) {
                $this->delete($v);
            }
        }

        return $this->set($name, '', 1);
    }

}
