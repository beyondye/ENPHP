<?php

declare(strict_types=1);

namespace System\Database\PDO;

use System\Database\DatabaseAbstract;
use System\Database\DatabaseException;

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
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_CLASS,
            \PDO::ATTR_EMULATE_PREPARES => true,
            \PDO::ATTR_PERSISTENT => $config['persistent'] ?? false,
        ];

        try {
            $this->db = new \PDO("sqlite:{$config['database']}", $config['username'], $config['password'], $options);
        } catch (\PDOException $e) {
            throw new DatabaseException('SQLite Database Connection Error :' . $e->getMessage());
        }
    }

   
}
