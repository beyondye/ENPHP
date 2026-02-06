<?php

declare(strict_types=1);

namespace system;

class Lang
{

    private static string $lang = LANG;

    public static function get(): string
    {
        return self::$lang;
    }
    public static function set(string $lang): void
    {
        self::$lang = $lang;
    }
}
