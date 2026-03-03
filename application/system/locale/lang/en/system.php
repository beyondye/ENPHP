<?php
return [
    'validator' => [
        'required' => '{label} NO NULL',
        'len' => '{label} LEN MUST BE {limit}',
        'minLen' => '{label} MIN LEN MUST BE {limit}',
        'maxLen' => '{label} MAX LEN MUST BE {limit}',
        'gt' => '{label} MUST GT {limit}',
        'lt' => '{label} MUST LT {limit}',
        'gte' => '{label} MUST GTE {limit}',
        'lte' => '{label} MUST LTE {limit}',
        'eq' => '{label} MUST EQ {limit}',
        'neq' => '{label} CANNOT EQ {limit}',
        'in' => '{label} MUST IN {limit}',
        'nin' => '{label} CANNOT IN {limit}',
        'same' => '{label} AND {limit} MUST SAME',
        'mobile' => '{label} FORMAT ERROR',
        'email' => '{label} FORMAT ERROR',
        'id' => '{label} FORMAT ERROR',
        'ip4' => '{label} FORMAT ERROR',
        'ip6' => '{label} FORMAT ERROR',
        'url' => '{label} FORMAT ERROR',
        'array' => '{label} MUST BE ARRAY',
        'float' => '{label} MUST BE FLOAT',
        'num' => '{label} MUST BE NUM',
        'string' => '{label} MUST BE STRING',
        'chinese' => '{label} MUST BE CHINESE',
        'alpha' => '{label} MUST BE ALPHA',
        'alphaNum' => '{label} MUST BE ALPHA NUM',
        'alphaNumChinese' => '{label} MUST BE ALPHA NUM CHINESE',
        'alphaNumDash' => '{label} MUST BE ALPHA NUM DASH',
        'default' => '{label} VALIDATION FAILED'
    ]
];
