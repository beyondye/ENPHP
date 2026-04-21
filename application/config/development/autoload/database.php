<?php
return [
    'pgsql' => [
        'driver' => 'pdo_pgsql',
        'host' => 'localhost',
        'port' => 5432,
        'database' => 'test_db',
        'username' => 'postgres',
        'password' => 'a12345678',
        'sslmode' => 'disable',
        'persistent' => false
    ],
    'default' => [
        'driver' => 'pdo_mysql',
        'host' => 'localhost',
        'port' => 3306,
        'database' => 'test_db',
        'username' => 'root',
        'password' => 'a12345678',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'persistent' => false
    ],
    'test' => [
        'driver' => 'pdo_mysql',
        'host' => 'localhost',
        'port' => 3306,
        'database' => 'test_db',
        'username' => 'root',
        'password' => 'a12345678',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'persistent' => false
    ],
     'unsupported' => [
        'driver' => 'unsupported_driver',
        'host' => 'localhost',
        'port' => 3306,
        'database' => 'test_db',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'persistent' => false
    ],
    'sqlite' => [
        'driver' => 'pdo_sqlite',
        'database' => ':memory:',
        'username' => '',
        'password' => '',
        'persistent' => false
    ]
   ];
