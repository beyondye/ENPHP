<?php
return [

    'test' => [
        'entry' => \app\service\Test::class,
        'params' => [
            'test' => [
                'type' => 'class',
                'value' => \app\model\Test::class,
                'params' => [
                    'db' => ['value' => 'database.default']
                ],
            ],
        ]
    ]


];
