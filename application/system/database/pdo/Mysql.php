<?php

declare(strict_types=1);

namespace system\database\pdo;

use system\database\DatabaseAbstract;
use system\database\DatabaseException;

class Mysql extends DatabaseAbstract
{
    use Common;

    private \PDO|null $db = null;

    protected array $config = [];

    public function __construct($config = [])
    {
        $this->config = $config;

        $dsn = "mysql:host={$config['host']};dbname={$config['database']};port={$config['port']};charset={$config['charset']};collation={$config['collation']}";
        $options = [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_CLASS,
            \PDO::ATTR_EMULATE_PREPARES => false,
            \PDO::ATTR_PERSISTENT => $config['persistent'] ?? false,
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
