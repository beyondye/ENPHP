<?php

namespace system;


class Middleware
{

    /**
     * 放置运行之前
     */
    public static function before()
    {
        foreach (MIDDLEWARE['before'] as $key => $value) {
            (new $value())->handle();
        }
    }

    /**
     * 放置运行之后
     */
    public static function after()
    {

        foreach (MIDDLEWARE['after'] as $key => $value) {
            (new $value())->handle();
        }
    }

}