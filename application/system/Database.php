<?php

namespace system;

class Database
{

    /**
     * 返回数据库实列
     *
     * @param string $service
     *
     * @return object
     */
    public static function instance(string $service = 'default')
    {
        static $ins = [];

        if (isset($ins[$service])) {
            return $ins[$service];
        }

        $config = include APP_DIR . 'config/' . ENVIRONMENT . '/database' . EXT;
        if (!isset($config[$service])) {
            exit(" '{$service}' Config Not Exist,Please Check Database Config File In '" . ENVIRONMENT . "' Directory.");
        }

        $arguments = $config[$service];

        if ($arguments['driver'] == 'mysqli') {

            $ins[$service] = new database\mysqli\Db($arguments);

        }

        return $ins[$service];
    }


}