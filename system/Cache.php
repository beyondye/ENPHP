<?php

namespace system;

/**
 * 缓存
 *
 * @author Ding<beyondye@gmail.com>
 *
 */
class Cache
{

    public static function instance(string $service)
    {
        static $ins = [];

        if (isset($ins[$service])) {
            return $ins[$service];
        }

        $config = include APP_DIR . 'config/' . ENVIRONMENT . '/cache' . EXT;
        if (!isset($config[$service])) {
            exit(" '{$service}' Config Not Exist,Please Check Cache Config File In '" . ENVIRONMENT . "' Directory.");
        }

        $arguments = $config[$service];


        if ($arguments['driver'] == 'file') {

            $ins[$service] = new cache\File($arguments);

        } else if ($arguments['driver'] == 'redis') {

            $ins[$service] = new cache\Redis($arguments);

        }

        return $ins[$service];
    }

}