<?php

namespace system;

class Config
{
    private static array $items = [];

    /**
     * 初始化：扫描并加载所有配置文件
     */
    public static function init(string $configPath)
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

    public static function get(string $key, $default = null)
    {
        $data = self::$items;
        foreach (explode('.', $key) as $segment) {
            if (!is_array($data) || !isset($data[$segment])) {
                return $default;
            }
            $data = $data[$segment];
        }
        return $data;
    }

    public static function flush()
    {
        self::$items = [];
    }

    public static function set(string $key, $value)
    {
        self::$items[$key] = $value;
    }
}
