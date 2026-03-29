<?php
return [
    'default' => [
        'driver' => 'pdo_mysql',
        'host' => 'localhost',
        'username' => 'root',
        'password' => '123456',
        'database' => 'test',
        'port' => 3306,
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'persistent' => true,
        
    ],


    'sqlite' => [
        'driver' => 'pod_sqlite',
        'host' => 'test.db',
        'username' => '',
        'password' => '',
        'database' => 'sqlite3',
        'database' => 'test',
        'port' => '',
        'charset' => 'utf8'
    ],


];
