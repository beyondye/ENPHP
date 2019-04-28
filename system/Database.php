<?php

namespace system;
/**
 * Database Factory
 *
 * @author Ding<beyondye@gmail.com>
 *
 */
class Database
{

    public static function instance($service)
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