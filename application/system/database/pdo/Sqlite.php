<?php

declare(strict_types=1);

namespace system\database\pdo;

use system\database\DatabaseAbstract;
use system\database\DatabaseException;

class Sqlite extends DatabaseAbstract
{
    use Common;

    private \PDO|null $db = null;

    public function __construct($config = [])
    {
        $config = array_merge([
            'persistent' => false,
            'username' => '',
            'password' => '',
            'database' => ':memory:',
            'driver' => 'pdo_sqlite'
        ], $config);

        if (empty($config['database'])) {
            throw new DatabaseException('SQLite Database Name Is Required.');
        }

        $options = [
            \PDO::ATTR_PERSISTENT => $config['persistent'] ?? false,
        ];

        try {
            profiler('benchmark', 'sqlite', $config['database']);
            $this->db = new \PDO("sqlite:{$config['database']}", $config['username'], $config['password'], $options);
            profiler('benchmark', 'sqlite');
        } catch (\PDOException $e) {
            throw new DatabaseException('SQLite Database Connection Error :' . $e->getMessage());
        }
    }
}
