<?php

declare(strict_types=1);

namespace system\database\pdo;

use system\database\DatabaseAbstract;
use system\database\DatabaseException;

class Pgsql extends DatabaseAbstract
{
    use Common;

    private \PDO|null $db = null;

    public function __construct($config = [])
    {
        $config = array_merge([
            'host' => 'localhost',
            'port' => 5432,
            'persistent' => false,
            'username' => '',
            'password' => '',
            'database' => '',
            'sslmode' => 'disable',
            'driver' => 'pdo_pgsql'
        ], $config);

        if (empty($config['database']) || empty($config['username']) || empty($config['password'])) {
            throw new DatabaseException('Database Name Or Username Or Password Is Required.');
        }

        $dsn = "pgsql:host={$config['host']};dbname={$config['database']};port={$config['port']};sslmode={$config['sslmode']}";
        $options = [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_CLASS,
            \PDO::ATTR_EMULATE_PREPARES => false,
            \PDO::ATTR_PERSISTENT => $config['persistent'] ?? false
        ];

        try {
            profiler('benchmark', 'database', $config['host']);
            $this->db = new \PDO($dsn, $config['username'], $config['password'], $options);
            profiler('benchmark', 'database');
        } catch (\PDOException $e) {
            throw new DatabaseException('Database Connection Error :' . $e->getMessage());
        }
    }


}
