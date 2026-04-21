<?php

declare(strict_types=1);

namespace system;

use system\database\DatabaseAbstract;
use system\database\DatabaseException;
use system\Config;

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
                $ins[$service] = new database\pdo\Mysql($config);
                break;
            case 'pdo_sqlite':
                $ins[$service] = new database\pdo\Sqlite($config);
                break;
            case 'pdo_pgsql':
                $ins[$service] = new database\pdo\Pgsql($config);
                break;
            default:
                throw new DatabaseException("'{$service}' Driver Not Support.");
        }

        return $ins[$service];
    }
}