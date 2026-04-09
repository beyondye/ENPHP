<?php

declare(strict_types=1);

namespace system\database;

use system\database\ResultAbstract;

abstract class DatabaseAbstract
{
    abstract public function execute(string $sql): int|ResultAbstract;

    abstract public function insert(string $table, array ...$data): string|int;

    abstract public function upsert(string $table, array $data): string|int;

    abstract public function update(string $table, array $data, array|int|string|float ...$wheres): int;

    abstract public function delete(string $table, array|int|string|float ...$wheres): int;

    abstract public function lastid(): int|string;

    abstract public function effected(): int;

    abstract public function commit(): bool;

    abstract public function rollback(): bool;

    abstract public function transaction(): bool;

    abstract public function select(string $table, array $field=[], array $where=[], array $groupby=[], array $having=[], array $orderby=[], int|array $limit=[]): ResultAbstract;

    abstract public function close(): bool;
}
