<?php

declare(strict_types=1);

namespace System;

class Cookie
{
    /**
     * 获取cookie数据,不存在返回null，不带参数返回全部
     *
     * @param string $name
     *
     * @return array|string|null
     */
    public static function get(string $name = ''): array|string|null
    {
        if ($name === '') {
            return $_COOKIE;
        }

        return $_COOKIE[$name] ?? null;
    }

    /**
     * 设置cookie输出
     *
     * @param string $name Cookie name
     * @param string|int $value Cookie value
     * @param int|array $options 过期秒数基于当前时间戳之上，设置0为关闭浏览器失效 | cookie选项 
     * @param string $options['path'] cookie有效路径
     * @param string $options['domain'] cookie有效域名
     * @param boolean $options['secure'] 是否必须https传输
     * @param boolean $options['httponly'] http唯一读取cookie
     * @param int $options['expires'] 过期时间戳
     * @param string $options['samesite'] 同源策略
     *
     * @return bool
     */
    public static function set(string $name, string|int $value, int|array $options = []): bool
    {

        if ($name === '') {
            return false;
        }

        $expires = 0;
        if (is_int($options)) {
            $expires = $options === 0 ? 0 : time() + $options;
            return setcookie($name, (string)$value, array_merge(COOKIE_OPTIONS, ['expires' => $expires]));
        }

        $options = array_merge(COOKIE_OPTIONS, $options);
        $options['expires'] = $options['expires'] === 0 ? 0 : time() + $options['expires'];
        return setcookie($name, $value, $options);
    }

    /**
     * 设置多个cookie输出
     *
     * @param array $data Cookie数据格式 ['name'=>'value','name2'=>'value2']
     * @param int|array $options 过期秒数基于当前时间戳之上，设置0为关闭浏览器失效
     * @param string $options['path'] 有效路径
     * @param string $options['domain'] 有效域名
     * @param boolean $options['secure'] 是否必须https传输
     * @param boolean $options['httponly'] http唯一读取cookie   
     *
     * @return bool
     */
    public static function many(array $data, int|array $options = []): bool
    {
        foreach ($data as $key => $val) {

            self::set((string)$key, (string)$val, $options);
        }

        return true;
    }

    /**
     * 删除一个cookie
     *
     * @param string|array $name
     *
     * @return bool
     */
    public static function delete(string|array $name): bool
    {
        if (is_array($name)) {
            foreach ($name as $k => $v) {
                if (is_string($v)) {
                    self::delete($v);
                }
            }
            return true;
        }

        return self::set($name, '', -3600);
    }
}
