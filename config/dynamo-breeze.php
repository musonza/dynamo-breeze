<?php

return [
    /*
    |--------------------------------------------------------------------------
    | DynamoDB Tables Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the table configurations for the application
    | including their access patterns, primary keys, and indexes.
    |
    */

    'tables' => [
        'example_table' => [
            'table_name' => env('DYNAMODB_TABLE', 'ExampleTable'),
            'partition_key' => 'exampleId',
            'sort_key' => null,
            'attributes' => [
                'exampleId' => 'S',
            ],
            'access_patterns' => [
                'ExampleAccessPattern' => [
                    'gsi_name' => 'GSI_Example',
                    'key_condition_expression' => 'exampleId = :exampleId_val',
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | AWS SDK Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure AWS SDK settings, which will be used when
    | interacting with AWS services like DynamoDB.
    |
    */

    'sdk' => [
        'region' => env('DYNAMODB_REGION', 'us-west-2'),
        'version' => env('DYNAMODB_VERSION', 'latest'),
        'credentials' => [
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
        ],
    ],
];
