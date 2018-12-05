<?php

namespace system;

/**
 * 输入类
 *
 * @author Ding<beyondye@gmail.com>
 */
class Input
{

    /**
     * 获取查询字符串,不存在返回null，不带参数返回全部
     *
     * @param string $name
     *
     * @param string $default
     *
     * @return array|string
     */
    public function get($name = null, $default = null)
    {

        if ($name === null) {
            return $_GET;
        }

        if ($name) {

            if (isset($_GET[$name]) && $_GET[$name] != '') {
                return $_GET[$name];
            }

            return $default;
        }

        return null;
    }

    /**
     * 获取请求类型方法
     *
     * @return string
     */
    public function method()
    {
        return $_SERVER['REQUEST_METHOD'];
    }


    /**
     * 获取前一页的地址
     *
     * @return string
     */
    public function referer()
    {

        return $_SERVER['HTTP_REFERER'];
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
            return array_map(function ($val) {
                return trim($val);
            }, $_POST);
        }

        return isset($_POST[$name]) ? trim($_POST[$name]) : null;
    }

    /**
     * 判断是否ajax请求
     *
     * @return bool
     */
    public function isAjax()
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
            return getenv('HTTP_CLIENT_IP');
        }

        if (getenv('HTTP_X_FORWARDED_FOR')) {
            return getenv('HTTP_X_FORWARDED_FOR');
        }

        if (getenv('HTTP_X_FORWARDED')) {
            return getenv('HTTP_X_FORWARDED');
        }

        if (getenv('HTTP_FORWARDED_FOR')) {
            return getenv('HTTP_FORWARDED_FOR');
        }

        if (getenv('HTTP_FORWARDED')) {
            return getenv('HTTP_FORWARDED');
        }

        if (getenv('REMOTE_ADDR')) {
            return getenv('REMOTE_ADDR');
        }

        return '0.0.0.0';
    }

}
