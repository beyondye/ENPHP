<?php

declare(strict_types=1);

namespace system;

use system\database\DatabaseAbstract;
use system\database\DatabaseException;

class Database
{
    public static function instance(string $service = 'default'): DatabaseAbstract
    {
        static $ins = [];
        if (isset($ins[$service])) {
            return $ins[$service];
        }

        $config = include APP_DIR . 'config/' . ENVIRONMENT . '/database' . EXT;
        if (!isset($config[$service])) {
            throw new DatabaseException(" '{$service}' Config Not Exist,Please Check Database Config File In '" . ENVIRONMENT . "' Directory.");
        }

        $arguments = $config[$service];
        if ($arguments['driver'] == 'pdo_mysql') {
            $ins[$service] = new database\pdo\Mysql($arguments);
        } else {
            throw new DatabaseException(" '{$service}' Driver Not Support.");
        }

        return $ins[$service];
    }
}
