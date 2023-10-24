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
            'credentials' => 'other_account',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Extending Query Parameter Mappings
    |--------------------------------------------------------------------------
    |
    | Here, you may specify additional query parameter mappings that aren't
    | included within the default set provided by DynamoBreeze. This extension
    | allows your application to leverage newer DynamoDB features, ensuring
    | comprehensive functionality while retaining the ease of use that
    | DynamoBreeze offers.
    |
    */
    'additional_query_mappings' => [
        'expression_attribute_names' => 'ExpressionAttributeNames',
    ],

    /*
    |--------------------------------------------------------------------------
    | AWS SDK Configuration
    |--------------------------------------------------------------------------
    |
<<<<<<< HEAD
    | Here you may configure AWS SDK settings.
    | Each array under the 'credentials' key
=======
    | This configuration file is dedicated to the AWS SDK settings. It's especially
    | crucial for services like DynamoDB. Each array under the 'credentials' key
>>>>>>> e06a2ea7b060c618b85fdeeb5ae7f3e594be4dfc
    | represents a different set of credentials that your application can use
    | when interacting with AWS services. The 'default' configuration is used
    | when no specific credential set is requested.
    |
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

        // 'another_set' => [
        //     // ...
        // ],
    ],
];
