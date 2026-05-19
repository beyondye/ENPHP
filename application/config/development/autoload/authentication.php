<?php
return [
    'session' => [
        'name' => 'session',
    ],
    'jwt' => [
        'name' => 'jwt',
        'mode' => 'header', //header or query
        'secret' => '123456',
        'expires' => 3600,
    ],
    'cookie' => [
        'name' => 'cookie',
        'secret' => '123456',
        'expires' => 3600,
    ]
];
