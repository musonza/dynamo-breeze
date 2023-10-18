# DynamoBreeze

A Laravel package for easily interacting with Amazon DynamoDB using a single-table approach and a facade for streamlined developer experience.

## Table of Contents

- [Installation](#installation)
- [Configuration](#configuration)
- [Example Usage](#example-usage)
- [Testing](#testing)
- [Contribution](#contribution)
- [License](#license)
- [Contact](#contact)

## Installation

### Via Composer

```bash
composer require musonza/dynamo-breeze
```

### Configuration

After installing the package, publish the configuration file by running:

```bash
php artisan vendor:publish --provider="Musonza\DynamoBreeze\DynamoBreezeServiceProvider" --tag="config"
```

## Example Usage

With the DynamoBreeze facade, you can interact with DynamoDB in a more expressive and straightforward manner. Here are examples demonstrating its usage:

Below is a fundamental example of how to interact with DynamoDB using the DynamoBreeze package. DynamoBreeze is designed to accommodate a single-table design principle, which means you can create a generic table that can host multiple entities and utilize various access patterns efficiently.

In your `dynamo-breeze.php` config file:

```php
return [
    // 'tables' holds the configuration for all the DynamoDB tables that this package will interact with.
    'tables' => [
        // Each table has its own configuration nested under a unique logical identifier used in your application code to reference the table configuration.
        'social_media' => [
            /*
            * 'table_name' is the name of the DynamoDB table as defined in AWS.
            */
            'table_name' => 'SocialMediaTable',

            /*
            * 'partition_key' specifies the primary key attribute name of the table.
            */
            'partition_key' => 'PK',

            /*
            * 'sort_key' specifies the sort key attribute name of the table.
            * If a table doesn't have a sort key, you can omit this field.
            */
            'sort_key' => 'SK',

            /*
            * 'attributes' define the attributes and their types that the model will interact with.
            * It's used for actions like creating tables or validating input.
            * Common types: 'S' => String, 'N' => Number, 'B' => Binary.
            */
            'attributes' => [
                'PK' => 'S',
                'SK' => 'S',
                // ...
            ],

            /*
            * 'access_patterns' define various access patterns to use with the table.
            * Each access pattern has a unique name and associated settings.
            */
            'access_patterns' => [
                'FetchUserPosts' => [
                    'gsi_name' => null,
                    'key_condition_expression' => 'PK = :pk_val AND begins_with(SK, :sk_prefix_val)',
                    'expression_attribute_values' => [
                        ':pk_val' => ['S' => 'USER#<user_id>'],
                        ':sk_prefix_val' => ['S' => 'POST#'],
                    ],
                ],
                'FetchPostComments' => [
                    'gsi_name' => null,
                    'key_condition_expression' => 'PK = :pk_val AND begins_with(SK, :sk_prefix_val)',
                    'expression_attribute_values' => [
                        ':pk_val' => ['S' => 'POST#<post_id>'],
                        ':sk_prefix_val' => ['S' => 'COMMENT#'],
                    ],
                ],
                // ...
            ],
            // ... additional settings for the table
        ],
        
        /*
        * Additional tables, such as 'products', can have similar configurations.
        * Adapt each table configuration to match its structure and access patterns in DynamoDB.
        */
        'products' => [
            // ... configuration for the 'products' table
        ],
        // ... configurations for other tables
    ],

    /*
    * 'sdk' holds the configuration for the AWS SDK.
    */
    'sdk' => [
        'region'   => env('DYNAMODB_REGION', 'us-west-2'),
        'version'  => env('DYNAMODB_VERSION', 'latest'),
        'credentials' => [
            'key'    => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
        ],
    ],
];
```

#### Fetch User Posts

```php
use Musonza\DynamoBreeze\Facades\DynamoBreeze;

$result = DynamoBreeze::table('social_media')
    ->accessPattern('FetchUserPosts', [
        ':pk_val' => 'USER#' . $userId,
        ':sk_val' => 'POST#',
    ])
    ->get();
```

#### Fetch Post Comments

```php
$comments = DynamoBreeze::table('social_media')
    ->accessPattern('FetchPostComments', [
        ':pk_val' => 'POST#' . $postId,
        ':sk_val' => 'COMMENT#',
    ])
    ->get();
```

#### Fetch Post Likes

```php
$likes = DynamoBreeze::table('social_media')
    ->accessPattern('FetchPostLikes', [
        ':pk_val' => 'POST#' . $postId,
        ':sk_val' => 'LIKE#',
    ])
    ->get();
```

#### Fetch Conversation Messages

```php
$messages = DynamoBreeze::table('social_media')
    ->accessPattern('FetchConversationMessages', [
        ':pk_val' => 'CONVERSATION#' . $conversationId,
        ':sk_val' => 'MESSAGE#',
    ])
    ->get();
```

Ensure that keys, table names, and access patterns align with your actual DynamoDB setup to avoid discrepancies or errors.

## Testing

`composer test`

## Contribution

Contributions are welcome and will be fully credited. Please see [CONTRIBUTING](.github/CONTRIBUTING.md) and [CODE_OF_CONDUCT](.github/CODE_OF_CONDUCT.md) for details.

## License

DynamoBreeze is open-sourced software licensed under the [MIT license](LICENSE.md).
