<?php

declare(strict_types=1);

namespace System;

use System\Database\DatabaseAbstract;
use System\Database\DatabaseException;
use System\Config;

class Database
{
    
    public static function instance(string $service = 'database.default'): DatabaseAbstract
    {
        static $ins = [];

        if (isset($ins[$service])) {
            return $ins[$service];
        }

        // 使用 Config 类获取数据库配置
        $config = Config::get($service);
      
        if ($config === null) {
            throw new DatabaseException("Database config '{$service}' not found");
        }

        if (!is_array($config)) {
            throw new DatabaseException("Invalid database config format");
        }

        $driver = $config['driver'] ?? '';

        switch ($driver) {
            case 'pdo_mysql':
                $ins[$service] = new Database\PDO\Mysql($config);
                break;
            case 'pdo_sqlite':
                $ins[$service] = new Database\PDO\Sqlite($config);
                break;
            case 'pdo_pgsql':
                $ins[$service] = new Database\PDO\Pgsql($config);
                break;
            default:
                throw new DatabaseException("'{$service}' Driver Not Support.");
        }

        return $ins[$service];
    }
}