<?php

return [
    'GSI1' => [
        'index_name' => 'GSI1',
        'key_schema' => [
            'partition_key' => 'GSI1PK',
            'sort_key' => 'GSI1SK',
        ],
        'attributes' => [
            'GSI1PK' => 'S',
            'GSI1SK' => 'S',
        ],
    ],
    // ... other GSIs ...
];
