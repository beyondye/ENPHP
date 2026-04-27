<?php

declare(strict_types=1);

namespace system;

class Output
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
     * @return string
     */
    public static function compress(string $string): string
    {
        // 存储需要保留的标签内容
        $preserved = [];

        // 匹配 <pre> 和 <textarea> 标签及其内容
        $pattern = '/(<(pre|textarea)[^>]*>)([\s\S]*?)(<\/\2>)/i';

        // 提取并存储这些标签内的内容
        $string = preg_replace_callback($pattern, function ($matches) use (&$preserved) {
            $key = '__PRESERVED_' . count($preserved) . '__';
            $preserved[$key] = $matches[0];
            return $key;
        }, $string);

        // 移除换行符和制表符
        $string = str_replace(["\r\n", "\n", "\t"], '', $string);

        // 构建替换模式
        $pattern = [
            "/> *([^ ]*) *</",  // 标签之间的多余空格
            "/\s+/",             // 多个连续空白字符
            "/\s+>/",            // 标签前的多余空格
            "/\s+</",            // 标签后的多余空格
            "/>\s+/",            // 标签后和内容前的多余空格
            "/<\s+/",            // 内容后和标签前的多余空格
            "/<!--[^!]*-->/",     // HTML 注释
            "/\/\*[^*]*\*\//s"   // CSS/JS 注释
        ];

        $replace = [
            ">\\1<",             // 保留标签间的内容
            " ",                 // 替换为单个空格
            ">",                 // 移除标签前的空格
            "<",                 // 移除标签后的空格
            ">",                 // 移除标签后和内容前的空格
            "<",                 // 移除内容后和标签前的空格
            "",                  // 移除 HTML 注释
            ""                   // 移除 CSS/JS 注释
        ];

        // 执行替换
        $string = preg_replace($pattern, $replace, $string);

        // 将保留的内容放回
        foreach ($preserved as $key => $value) {
            $string = str_replace($key, $value, $string);
        }

        return $string;
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
    public static function view(string $_view, array $_data = [], bool $_return = false, bool $_compress = false)
    {
        static $_vars = [];
        $_vars = array_merge($_vars, $_data);
        extract($_vars);

        ob_start();
        include TEMPLATE_DIR . $_view . EXT;

        if ($_return) {
            $_buffer = ob_get_contents();
            ob_end_clean();
            if ($_compress) {
                return self::compress($_buffer);
            }
            return $_buffer;
        }

        if (ob_get_level() > 2) {
            ob_end_flush();
        } else {
            $_content = ob_get_contents();
            ob_end_clean();
            if ($_compress) {
                $_content = self::compress($_content);
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
     * @param array|object $data 需要返回的数据
     * @param bool $return 是否直接返回内容
     *
     * @return string
     */
    public static function json(int $status, string $message, $data = [], bool $return = false)
    {
        $content = [
            'status'  => $status,
            'message' => $message,
            'data'    => $data,
            'timestamp' => time()
        ];

        $json = json_encode($content, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_IGNORE);
        if ($return) {
            return $json;
        } else {
            header('Content-Type:application/json;charset=' . CHARSET);
            echo $json;
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
    public static function redirect(string $uri = '', int $http_response_code = 302): void
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
    public static function status(int $http_status_code): void
    {
        if (isset(self::HTTP_STATUS_CODE[$http_status_code])) {
            header(self::HTTP_STATUS_CODE[$http_status_code], true, $http_status_code);
            return;
        }
        header("HTTP/1.1 500 Internal Server Error", true, 500);
    }


    /**
     * 错误页面
     *
     * @param string 错误页面模版名
     * @param array 数据数组
     *
     * @return void
     */
    public static function error($code = 500, string $name = 'error/general', array $data = ['heading' => 'Internal Server Error', 'message' => 'An internal error has occurred.']): void
    {
        self::status($code);
        try {
            echo self::view($name, $data, true, true);
        } catch (\Exception $e) {
            header('Content-Type:text/html;charset=' . CHARSET);
            die('<h1>' . $code . '</h1><p>' . $e->getMessage() . '</p>');
        }
    }

    /**
     * 生成url链接
     *
     * @param string $action
     * @param array $param
     * @param string $anchor
     *
     * @return string
     */
    public static function url(string $action = '', array $param = [], string $anchor = ''): string
    {
       
        $anchor = $anchor == '' ? '' : '#' . $anchor;

        $key = '/';
        if ($param) {
            $key = '/' . join('/', array_keys($param));
        }

        if (array_key_exists($key, URL[$action])) {
            $temp = URL[$action][$key];
            $url = '';
            foreach ($param as $k => $v) {
                if ($url) {
                    $url = str_replace('{' . $k . '}', $v, $url);
                } else {
                    $url = str_replace('{' . $k . '}', $v, $temp);
                }
            }

            if ($url == '') {
                $url = $temp;
            }

            return $url . $anchor;
        }

        return $anchor;
    }
}
