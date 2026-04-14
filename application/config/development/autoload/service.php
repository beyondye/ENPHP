<?php
return [

    'test' => [
        'entry' => \service\Test::class,
        'params' => [
            'test' => [
                'type' => 'class',
                'value' => \model\Test::class,
                'params' => [
                    'db' => ['value' => 'database.default']
                ],
            ],
        ]
    ]


];
