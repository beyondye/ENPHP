<?php

namespace System;

/**
 * 输入类
 * 
 * @author Ye Ding <beyondye@gmail.com>
 */
class Input
{

    /**
     * 获取查询字符串,不存在返回null，不带参数返回全部
     * 
     * @param string $name
     * 
     * @return array|null
     */
    public function get($name = null)
    {

        if ($name === null) {
            return $_GET;
        }

        return isset($_GET[$name]) ? strip_tags($_GET[$name]) : null;
    }

    /**
     * 
     * 获取请求中的body
     * 
     * @return string
     */
    public function body()
    {
        return file_get_contents("php://input");
    }

    /**
     * 获取post数据,不存在返回null，不带参数返回全部
     * 
     * @param string $name
     * 
     * @return array|null
     */
    public function post($name = null)
    {

        if ($name === null) {
            return $_POST;
        }

        return isset($_POST[$name]) ? $_POST[$name] : null;
    }

    /**
     * 获取cookie数据,不存在返回null，不带参数返回全部
     * 
     * @param string $name
     * 
     * @return array|null
     */
    public function cookie($name = null)
    {

        if ($name === null) {
            return $_COOKIE;
        }

        return isset($_COOKIE[$name]) ? $_COOKIE[$name] : null;
    }

    /**
     * 判断是否ajax请求
     * 
     * @return bool
     */
    public function isAjaxRequest()
    {

        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            return ($_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest');
        } else {
            return false;
        }
    }


    /**
     * 获取ip
     * 
     * @return $ipaddress string 
     */
    public function ip()
    {

        if (getenv('HTTP_CLIENT_IP')) {
            $ipaddress = getenv('HTTP_CLIENT_IP');
        } else if (getenv('HTTP_X_FORWARDED_FOR')) {
            $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
        } else if (getenv('HTTP_X_FORWARDED')) {
            $ipaddress = getenv('HTTP_X_FORWARDED');
        } else if (getenv('HTTP_FORWARDED_FOR')) {
            $ipaddress = getenv('HTTP_FORWARDED_FOR');
        } else if (getenv('HTTP_FORWARDED')) {
            $ipaddress = getenv('HTTP_FORWARDED');
        } else if (getenv('REMOTE_ADDR')) {
            $ipaddress = getenv('REMOTE_ADDR');
        } else {
            $ipaddress = '0.0.0.0';
        }

        return $ipaddress;
    }

}
