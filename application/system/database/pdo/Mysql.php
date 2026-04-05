<?php

declare(strict_types=1);

namespace system\database\pdo;

use system\database\DatabaseAbstract;
use system\database\DatabaseException;

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
        ];

        try {
            profiler('benchmark', 'database', $config['host']);
            $this->db = new \PDO($dsn, $config['username'], $config['password'], $options);
            profiler('benchmark', 'database');
        } catch (\PDOException $e) {
            throw new DatabaseException('Database Connection Error :' . $e->getMessage());
        }
    }


    public function upsert(string $table, array $data): string|int
    {
        if (trim($table) === '') {
            throw new DatabaseException('Upsert Table Name Is Required.');
        }
        
        if (empty($data)) {
            throw new DatabaseException('Upsert Data Is Empty.');
        }

        $fields = array_keys($data);
        $placeholders = ':' . implode(', :', $fields);
        $updateFields = [];
        foreach ($fields as $field) {
            $updateFields[] = "{$field} = VALUES({$field})";
        }

        $sql = "INSERT INTO {$table} (" . implode(',', $fields) . ") VALUES ({$placeholders})";
        if (!empty($updateFields)) {
            $sql .= " ON DUPLICATE KEY UPDATE " . implode(', ', $updateFields);
        }

        try {
            $stmt = $this->db->prepare($sql);
            foreach ($data as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }
            $stmt->execute();
        } catch (\PDOException $e) {
            throw new DatabaseException('Upsert Execute Error :' . $e->getMessage());
        }

        $this->effected = $stmt->rowCount();

        return $this->lastid();
    }
}
