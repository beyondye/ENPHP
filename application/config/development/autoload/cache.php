<?php

return [
    'redis' => ['driver' => 'redis', 'service' => 'default'],
    //默认
    'default' => ['driver' => 'file', 'dir' => APP_DIR . 'cache/.data/', 'mode' => 0777]
];
