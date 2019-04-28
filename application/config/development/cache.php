<?php

return [
    //默认
    'default' => ['driver' => 'redis', 'host' => 'set.redis.to.hosts.file', 'port' => 6379, 'password' => '', 'database' => 0, 'timeout' => 30, 'serialization' => true],
    'file' => ['driver' => 'file', 'dir' => APP_DIR . 'cache/'],
    'apc' => ['host' => 'set.redis.to.hosts.file', 'port' => 6379, 'password' => '', 'database' => 0, 'timeout' => 30, 'serialization' => true],
];
