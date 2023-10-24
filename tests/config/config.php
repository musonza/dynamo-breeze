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
                    'index_name' => 'GSI_UserTimestamp',
                    'key_condition_expression' => 'UserId = :userIdVal',
                    'filter_expression' => null,
                    'expression_attribute_values' => null,
                ],
                'ExampleAccessPatternWithFilter' => [
                    'index_name' => 'GSI_UserTimestamp',
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
            'credentials' => 'other_account',
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
            'credentials' => 'default',
        ],
    ],

    /*
    * 'credentials' holds the different AWS credential sets.
    */
    'credentials' => [
        'default' => [ // Default credential set
            'region' => env('DYNAMODB_REGION', 'us-west-2'),
            'version' => env('DYNAMODB_VERSION', 'latest'),
            'endpoint' => env('DYNAMODB_ENDPOINT'),
            'credentials' => [
                'key' => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY'),
            ],
        ],

        'other_account' => [ // Credentials for another AWS account
            'region' => env('DYNAMODB_OTHER_REGION', 'us-east-1'),
            'version' => env('DYNAMODB_VERSION', 'latest'),
            'endpoint' => env('DYNAMODB_ENDPOINT'),
            'credentials' => [
                'key' => env('AWS_OTHER_ACCESS_KEY_ID'),
                'secret' => env('AWS_OTHER_SECRET_ACCESS_KEY'),
            ],
        ],

        // ... potentially more credential sets
    ],

    'additional_query_mappings' => [
        'expression_attribute_names' => 'ExpressionAttributeNames',
    ],
];
