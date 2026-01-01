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

     public static function set(string $key, $value): void
    {
        self::$keys[$key] = $value;
    }   

    public static function has(string $key): bool
    {
        return isset(self::$keys[$key]);
    }

     public static function remove(string $key): void
    {
        unset(self::$keys[$key]);
    }

    public function clear(): void
    {
        self::$keys = [];
    }


    public function __destruct()
    {
        $this->clear();
    }


}