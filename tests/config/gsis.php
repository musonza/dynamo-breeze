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
    'GSI2' => [
        'index_name' => 'GSI2',
        'key_schema' => [
            'partition_key' => 'GSI2PK',
            'sort_key' => 'GSI2SK',
        ],
        'attributes' => [
            'GSI2PK' => 'S',
            'GSI2SK' => 'N',
        ],
        'provisioned_throughput' => [
            'ReadCapacityUnits' => 10,
        ],
        'projection' => [
            'ProjectionType' => 'KEYS_ONLY',
        ],
    ],
    // ... other GSIs ...
];
