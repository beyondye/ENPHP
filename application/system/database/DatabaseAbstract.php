<?php

declare(strict_types=1);

namespace system\database;

use system\database\ResultAbstract;

abstract class DatabaseAbstract
{
    abstract public function execute(string $sql): int;

    abstract public function insert(string $table, array ...$data): int;

    abstract public function upsert(string $table, array $data): int;

    abstract public function update(string $table, array $data, array ...$wheres): int;

    abstract public function delete(string $table, array $wheres): int;

    abstract public function lastid(): int|string;

    abstract public function effected(): int;

    abstract public function commit(): bool;

    abstract public function rollback(): bool;

    abstract public function transaction(): bool;

     abstract public function select(string $table, array $params): ResultAbstract;

    abstract public function close(): bool;


}
