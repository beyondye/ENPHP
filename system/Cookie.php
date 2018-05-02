<?php

namespace System;

/**
 * cookie类
 * 
 * @author Ding <beyondye@gmail.com>
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
     * @param array $data   Cookie数据格式 ['name'=>'value','name2'=>'value2']
     * @param int $expire   过期秒数基于当前时间戳之上，设置0为关闭浏览器失效
     * @param string $path
     * @param string $domain
     * @param string $secure
     * @param string $httponly
     * 
     * @return boolean
     */
    public function set($data, $expire = COOKIE_EXPIRE, $path = COOKIE_PATH, $domain = COOKIE_DOMAIN, $secure = COOKIE_SECURE, $httponly = COOKIE_HTTPONLY)
    {
        if (!is_array($data)) {
            return false;
        }

        $init = [
            'expire' => intval($expire) == 0 ? 0 : time() + intval($expire),
            'path' => $path,
            'domain' => $domain,
            'secure' => $secure,
            'httponly' => $httponly
        ];

        foreach ($data as $key => $val) {
            $result = array_merge($init, ['name' => $key, 'value' => $val]);
            setcookie($result['name'], $result['value'], $result['expire'], $result['path'], $result['domain'], $result['secure'], $result['httponly']);
        }

        return true;
    }

}
