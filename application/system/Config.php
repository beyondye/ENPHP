<?php

namespace system;

class Config
{
    private  static array $items = [];

    /**
     * 初始化：扫描并加载所有配置文件
     */
    public static function init(string $configPath): void
    {
        if (!is_dir($configPath)) {
            throw new \system\SysException("Config path '$configPath' is not a directory");
        }

        $files = glob($configPath . '/*.php');

        foreach ($files as $file) {
            $key = basename($file, '.php');
            $data = include $file;
            if (is_array($data)) {
                self::$items[$key] = $data;
            }
        }
    }

    public static function get(string $key, $default = null): mixed
    {
        if (empty(self::$items)) {
            return $default;
        }

        if (trim($key) == '') {
            return $default;
        }

        $segments = explode('.', $key);

        $data = self::$items;
        foreach ($segments as $segment) {
            if (isset($data[$segment])) {
                $data = $data[$segment];
            } else {
                return $default;
            }
        }
        return $data;
    }

    public static function flush(string $key): void
    {
        if (isset(self::$items[$key])) {
            unset(self::$items[$key]);
        }
    }

    public static function set(string $key, $value): void
    {
        if (trim($key) == '') return;

        $segments = explode('.', $key);
        $data = &self::$items; // 使用引用 &

        foreach ($segments as $segment) {
           
            if (!isset($data[$segment]) || !is_array($data[$segment])) {
                $data[$segment] = [];
            }
            $data = &$data[$segment]; // 移动引用到下一层
        }

        $data = $value; 
    }
}
