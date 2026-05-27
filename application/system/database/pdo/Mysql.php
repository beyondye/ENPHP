<?php

declare(strict_types=1);

namespace System\Database\PDO;

use System\Database\DatabaseAbstract;
use System\Database\DatabaseException;

class Mysql extends DatabaseAbstract
{
    use Common;

    private \PDO|null $db = null;

    public function __construct($config = [])
    {
        $config = array_merge([
            'host' => 'localhost',
            'port' => 3306,
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'persistent' => false,
            'username' => '',
            'password' => '',
            'database' => '',
            'driver' => 'pdo_mysql'
        ], $config);

        if (empty($config['database']) || empty($config['username']) || empty($config['password'])) {
            throw new DatabaseException('Database Name Or Username Or Password Is Required.');
        }

        $dsn = "mysql:host={$config['host']};dbname={$config['database']};port={$config['port']};charset={$config['charset']};collation={$config['collation']}";
        $options = [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_CLASS,
            \PDO::ATTR_EMULATE_PREPARES => false,
            \PDO::ATTR_PERSISTENT => $config['persistent'] ?? false,
            \PDO::MYSQL_ATTR_FOUND_ROWS => true,
        ];

        try {
            $this->db = new \PDO($dsn, $config['username'], $config['password'], $options);
        } catch (\PDOException $e) {
            throw new DatabaseException('MySQL Database Connection Error :' . $e->getMessage());
        }
    }

}
