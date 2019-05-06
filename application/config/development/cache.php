<?php

return [
    'redis' => ['driver' => 'redis', 'host' => 'set.redis.to.hosts.file', 'port' => 6379, 'password' => '', 'database' => 0, 'timeout' => 30, 'serialization' => true],
    //默认
    'default' => ['driver' => 'file', 'dir' => APP_DIR . 'cache/.data/', 'mode' => 0777]
];
