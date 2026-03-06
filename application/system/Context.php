<?php
declare(strict_types=1);

namespace system;

class Context
{
    private static array $keys = [];
   
    public static function get(string $key): mixed
    {
        return self::$keys[$key] ?? null;
    }           

     public static function set(string $key, mixed $value): void
    {
        self::$keys[$key] = $value;
    }   

    public static function has(string $key): bool
    {
        return array_key_exists($key, self::$keys);
    }

     public static function remove(string $key): void
    {
        unset(self::$keys[$key]);
    }

    public static function clear(): void
    {
        self::$keys = [];
    }

}