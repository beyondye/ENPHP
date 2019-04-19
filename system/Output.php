<?php

namespace system;

use system\System;

/**
 * 输出类
 *
 * @author Ding<beyondye@gmail.com>
 */
class Output extends System
{

    /**
     * http状态码
     *
     * @var array
     */
    const HTTP_STATUS_CODE = [
        100 => "HTTP/1.1 100 Continue",
        101 => "HTTP/1.1 101 Switching Protocols",
        200 => "HTTP/1.1 200 OK",
        201 => "HTTP/1.1 201 Created",
        202 => "HTTP/1.1 202 Accepted",
        203 => "HTTP/1.1 203 Non-Authoritative Information",
        204 => "HTTP/1.1 204 No Content",
        205 => "HTTP/1.1 205 Reset Content",
        206 => "HTTP/1.1 206 Partial Content",
        300 => "HTTP/1.1 300 Multiple Choices",
        301 => "HTTP/1.1 301 Moved Permanently",
        302 => "HTTP/1.1 302 Found",
        303 => "HTTP/1.1 303 See Other",
        304 => "HTTP/1.1 304 Not Modified",
        305 => "HTTP/1.1 305 Use Proxy",
        307 => "HTTP/1.1 307 Temporary Redirect",
        400 => "HTTP/1.1 400 Bad Request",
        401 => "HTTP/1.1 401 Unauthorized",
        402 => "HTTP/1.1 402 Payment Required",
        403 => "HTTP/1.1 403 Forbidden",
        404 => "HTTP/1.1 404 Not Found",
        405 => "HTTP/1.1 405 Method Not Allowed",
        406 => "HTTP/1.1 406 Not Acceptable",
        407 => "HTTP/1.1 407 Proxy Authentication Required",
        408 => "HTTP/1.1 408 Request Time-out",
        409 => "HTTP/1.1 409 Conflict",
        410 => "HTTP/1.1 410 Gone",
        411 => "HTTP/1.1 411 Length Required",
        412 => "HTTP/1.1 412 Precondition Failed",
        413 => "HTTP/1.1 413 Request Entity Too Large",
        414 => "HTTP/1.1 414 Request-URI Too Large",
        415 => "HTTP/1.1 415 Unsupported Media Type",
        416 => "HTTP/1.1 416 Requested range not satisfiable",
        417 => "HTTP/1.1 417 Expectation Failed",
        500 => "HTTP/1.1 500 Internal Server Error",
        501 => "HTTP/1.1 501 Not Implemented",
        502 => "HTTP/1.1 502 Bad Gateway",
        503 => "HTTP/1.1 503 Service Unavailable",
        504 => "HTTP/1.1 504 Gateway Time-out"
    ];

    /**
     * 压缩HTML
     *
     * @param string $string
     *
     * @return string
     */
    public function compress($string)
    {
        $string = str_replace("\r\n", '', $string);
        $string = str_replace("\n", '', $string);
        $string = str_replace("\t", '', $string);

        $pattern = ["/> *([^ ]*) *</", "/\s+/", "/\s+>/", "/\s+</", "/>\s+/", "/<\s+/", "/<!--[^!]*-->/", "'/\*[^*]*\*/'"];
        $replace = [">\\1<", " ", ">", "<", ">", "<", "", ""];

        return preg_replace($pattern, $replace, $string);
    }

    /**
     * 输出视图
     *
     * @staticvar array $_vars
     *
     * @param string $_view 视图名称
     * @param array $_data 视图数据
     * @param boolean $_return 是否返回内容
     * @param boolean $_compress 是否压缩HTML
     *
     * @return string
     */
    public function view($_view, $_data = [], $_return = false, $_compress = false)
    {
        static $_vars = [];
        $_vars = array_merge($_vars, $_data);
        extract($_vars);

        ob_start();
        include APP_DIR . 'template/' . TEMPLATE . '/' . $_view . EXT;

        if ($_return) {

            $_buffer = ob_get_contents();
            ob_end_clean();

            if ($_compress) {
                return $this->compress($_buffer);
            }

            return $_buffer;
        }

        if (ob_get_level() > 2) {
            ob_end_flush();
        } else {

            $_content = ob_get_contents();
            ob_end_clean();

            if ($_compress) {
                $_content = $this->compress($_content);
            }

            header('Content-Type:text/html;charset=' . CHARSET);
            echo $_content;
        }
    }

    /**
     * 输出json数据
     *
     * @param int $status 自定义状态码
     * @param string $message 状态简单描述
     * @param array $data 需要返回的数据
     * @param bool $return 是否直接返回内容
     *
     * @return json string
     */
    public function json($status, $message, $data = [], $return = false)
    {
        $content = ['status' => $status, 'message' => $message, 'data' => $data];

        if ($return) {
            return json_encode($content);
        } else {
            header('Content-Type:application/json;charset=' . CHARSET);
            echo json_encode($content);
        }
    }

    /**
     * 重定向地址
     *
     * @param string $uri 地址
     * @param int $http_response_code HTTP状态码
     *
     * @return void
     */
    public function redirect($uri = '', $http_response_code = 302)
    {
        header("Location: " . $uri, true, $http_response_code);
        exit;
    }

    /**
     * 设置HTTP状态码
     *
     * @param int $http_status_code
     *
     * @return void
     *
     */
    public function status($http_status_code)
    {
        if (isset(self::HTTP_STATUS_CODE[$http_status_code])) {
            header(self::HTTP_STATUS_CODE[$http_status_code], true);
        }

        header('Unknown Status');
    }

    /**
     * 错误页面
     *
     * @param string 错误页面模版名
     * @param array 数据数组
     */
    public function error($name = 'general', $data = ['heading' => 'Error Message', 'message' => 'An error occurred.'])
    {

        extract($data);

        ob_start();
        include APP_DIR . 'error/' . $name . EXT;
        $_content = ob_get_contents();
        ob_end_clean();
        $_content = $this->compress($_content);

        header('Content-Type:text/html;charset=' . CHARSET);
        echo $_content;
    }

}
