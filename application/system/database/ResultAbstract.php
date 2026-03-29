<?php
namespace system\database;

abstract class ResultAbstract
{
    abstract public function all(string $type = 'object'): array;
    abstract public function count(): int;   
    abstract public function first(string $type = 'object'): array|object|null;

}   
