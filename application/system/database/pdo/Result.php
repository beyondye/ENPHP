<?php

namespace system\database\pdo;

use system\database\ResultAbstract;

class Result extends ResultAbstract
{
    protected \PDOStatement|null $stmt;

    protected int $num = 0;

    public function __construct(\PDOStatement $stmt)
    {
        $this->stmt = $stmt;
    }

    public function all(string $type = 'object'): array
    {
        if ($type == 'object') {
            $type = \PDO::FETCH_CLASS;
        } else {
            $type = \PDO::FETCH_ASSOC;
        }

        $result = $this->stmt->fetchAll($type);
        $this->num = count($result);

        return $result;
    }

    public function count(): int
    {
        return $this->num;
    }

    public function first(string $type = 'object'): array|object|null
    {

        if ($type == 'object') {
            $type = \PDO::FETCH_CLASS;
        } else {
            $type = \PDO::FETCH_ASSOC;
        }

        $result = $this->stmt->fetch($type);

        $this->stmt->closeCursor();

        return $result;
    }
}
