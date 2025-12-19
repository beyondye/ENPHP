<?php

namespace system\database;

abstract class Dbabstract
{
    abstract public function execute(string $sql);
    abstract public function insert(string $table, array $data);
    abstract public function replace(string $table, array $data);
    abstract public function update(string $table, array $data, array $wheres);
    abstract public function delete(string $table, array $wheres);
    abstract public function lastid();
  
    // abstract public function begin();
    // abstract public function commit();
    // abstract public function rollback();
    // abstract public function transaction();

    abstract public function select(string $table, array $params);
    abstract public function close();
}