<?php

return [
    'tables' => [
        'example_table' => [
            'table_name' => 'ExampleTable',
            'partition_key' => 'PostId',
            'sort_key' => 'Timestamp',
            'attributes' => [
                'PostId' => 'S',   // String
                'Timestamp' => 'N', // Number
            ],
            'access_patterns' => [
                'ExampleAccessPattern' => [
                    'gsi_name' => 'GSI_UserTimestamp',
                    'key_condition_expression' => 'UserId = :userIdVal',
                    'filter_expression' => null,
                    'expression_attribute_values' => null,
                ],
                'ExampleAccessPatternWithFilter' => [
                    'gsi_name' => 'GSI_UserTimestamp',
                    'key_condition_expression' => 'UserId = :userIdVal',
                    'filter_expression' => 'Age > :minAge',
                    'expression_attribute_values' => [
                        ':pk_val' => ['S' => 'USER#<user_id>'],
                        ':minAge' => ['N' => '<age>'],
                    ],
                ],
                'FetchUserPosts' => [
                    'key_condition_expression' => 'PK = :pk_val AND SK = :sk_val',
                    'expression_attribute_values' => [
                        ':pk_val' => ['S' => 'USER#<user_id>'],
                        ':sk_val' => ['N' => '<timestamp>'],
                    ],
                ],
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | SocialMediaTable
        |--------------------------------------------------------------------------
        |
        | Unlike normal library tests we have tried to include a real world example
        | in our tests to help drive home the single table approach as well
        | as enable a greater understanding for the package.
        |
        | This is an example social media platform that. Some of the entinties we have are:
        | Users, Posts, Comments, Likes, Messages
        |
        |
        */
        'social_media' => [
            'table_name' => 'SocialMediaTable',
            'partition_key' => 'PK',
            'sort_key' => 'SK',
            'attributes' => [
                'PK' => 'S',
                'SK' => 'S',
            ],
            'access_patterns' => require 'access_patterns.php',
            'global_secondary_indexes' => require 'gsis.php',
        ],
    ],

    'sdk' => [
        'region' => env('DYNAMODB_REGION', 'us-west-2'),
        'version' => env('DYNAMODB_VERSION', 'latest'),
        'credentials' => [
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
        ],
    ],
];
