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
        static $config = [];

        if (isset($ins[$service])) {
            return $ins[$service];
        }

        if ($config === []) {
            $path = APP_DIR . 'config/' . ENVIRONMENT . '/database' . EXT;
            if (!file_exists($path)) {
                throw new DatabaseException("Database config file not found: {$path}");
            }

            $config = include $path;
            if (!is_array($config)) {
                throw new DatabaseException("Invalid database config format");
            }
        }

        if (!isset($config[$service])) {
            throw new DatabaseException(" '{$service}' Config Not Exist,Please Check Database Config File In '" . ENVIRONMENT . "' Directory.");
        }

        $arguments = $config[$service];
        $driver = $arguments['driver'] ?? '';

        switch ($driver) {
            case 'pdo_mysql':
                $ins[$service] = new database\pdo\Mysql($arguments);
                break;
            case 'pdo_sqlite':
                $ins[$service] = new database\pdo\Sqlite($arguments);
                break;
            default:
                throw new DatabaseException(" '{$service}' Driver Not Support.");
        }

        return $ins[$service];
    }
}
