# DynamoBreeze

**DynamoBreeze** is a Laravel package designed to simplify interactions with Amazon DynamoDB. While it accommodates the single-table design principle, it's versatile enough to support multiple-table usage, providing a seamless experience regardless of your database's architecture. Importantly, it enables the use of multiple AWS credentials, a key feature for applications requiring access to tables across different AWS accounts or those utilizing specific IAM keys for certain tables. Through a fluent, expressive facade, DynamoBreeze makes it easier than ever to work with DynamoDB.

## Key Features

- **Single or Multiple Table Support**: Whether you're adhering to a single-table design or using multiple tables, DynamoBreeze adapts to your needs, allowing for efficient interactions with your data without the complexity.
- **Multiple AWS Credentials**: DynamoBreeze's architecture facilitates the use of different AWS credentials for various tables, perfect for interacting with multiple AWS accounts or applying specific keys and secrets to individual tables. This capability ensures a flexible and secure approach to managing your data across diverse environments.
- **Expressive Syntax**: Leverage the power of fluent syntax to build your DynamoDB queries, making them more readable and maintainable.
- **Streamlined Configuration**: Define your table structures and access patterns in a central configuration, making it easy to manage and query your data.

- **Customizable**: Ready to be used out of the box, but built with customization in mind, so you can adjust it according to your application's requirements.

## Table of Contents

- [Installation](#installation)
- [Configuration](#configuration)
- [Example Usage](#example-usage)
- [Handling Responses with DynamoBreezeResult](#handling-responses-with-dynamobreezeresult)
- [Extending Query Parameter Mappings](#extending-query-parameter-mappings)
- [Pagination](#pagination)
- [Batch Retrieval with batchGet](#batch-retrieval-with-batchget)
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
            'credentials' => 'other_account',
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
```

#### Fetch User Posts

```php
use Musonza\DynamoBreeze\Facades\DynamoBreeze;

$result = DynamoBreeze::withTableIdentifier('social_media')
    ->accessPattern('FetchUserPosts', [
        'user_id' => $userId,
    ])
    ->get();
```

#### Fetch Post Comments

```php
$comments = DynamoBreeze::withTableIdentifier('social_media')
    ->accessPattern('FetchPostComments', [
        'post_id' => $postId,
    ])
    ->get();
```

#### Fetch Post Likes

```php
$likes = DynamoBreeze::withTableIdentifier('social_media')
    ->accessPattern('FetchPostLikes', [
        'post_id' => $postId,
    ])
    ->get();
```

#### Fetch Conversation Messages

```php
$messages = DynamoBreeze::withTableIdentifier('social_media')
    ->accessPattern('FetchConversationMessages', [
        'conversation_id' => $conversationId,
    ])
    ->get();
```

## Handling Responses with DynamoBreezeResult

All operations performed through the DynamoBreeze facade will return an instance of `DynamoBreezeResult`. This object provides a convenient way to interact with the data returned from DynamoDB, offering several methods to retrieve specific portions of the AWS result or the entire raw result.

### Methods Available

- `getItems()`: Returns an array of items from the result. If no items are found, it returns null.

- `getCount()`: Retrieves the count of returned items. If no count is provided in the result, it returns null.

- `getRawResult()`: Provides access to the original `Aws\Result` object, allowing you to retrieve any data or metadata returned from the AWS SDK's DynamoDB client.

### Example

```php
use Musonza\DynamoBreeze\Facades\DynamoBreeze;

// Perform an operation
$result = DynamoBreeze::withTableIdentifier('social_media')
    ->accessPattern('FetchUserPosts', [
        'user_id' => $userId,
    ])
    ->get();

// Get the items returned from DynamoDB
$items = $result->getItems();

// Get the count of items
$count = $result->getCount();

// Access the raw AWS SDK result object
$rawResult = $result->getRawResult();
```

## Extending Query Parameter Mappings

DynamoBreeze provides a core set of mappings that translate specific configuration keys to the corresponding DynamoDB query parameters. These mappings are used internally to build queries from your application's configurations.

While the default mappings cover a wide range of common use cases, there might be scenarios where you need to extend or override these mappings. For such cases, DynamoBreeze offers a flexible solution through the `additional_query_mappings` configuration.

### Adding Custom Mappings

If you need to add new mappings that aren't included in the default set, you can define them in your application's `dynamo-breeze` configuration file under the `additional_query_mappings` key.

Here's an example of how to set up `additional_query_mappings:`

```php
// In your config/dynamo-breeze.php configuration file

return [
    'tables' => [],
    // ... other configuration values ...

    'additional_query_mappings' => [
        'your_config_key' => 'DynamoQueryParam',
        // other custom mappings...
    ],
];
```

In this example, `your_config_key` is the key you use in your application's configuration, and `DynamoQueryParam` is the corresponding parameter that DynamoDB expects in a query.

#### Use Case

A practical use case for adding custom mappings could be when you're using a newer feature of DynamoDB that isn't yet covered by DynamoBreeze's default mappings. By adding the necessary mapping, you ensure your application can take advantage of all DynamoDB features while maintaining the convenience of using DynamoBreeze.

## Pagination

DynamoDB does not return all items in a single response; instead, it paginates the results. DynamoBreeze simplifies the pagination process, allowing you to easily navigate through your records. This is especially useful when dealing with large data sets.

### Example Pagination

Imagine you want to retrieve all posts for a specific user, but due to the size of the data, DynamoDB paginates the results. Here's how you can handle pagination with DynamoBreeze:

```php
$startKey = null;
$retrievedItemsCount = 0;
$pageSize = 10; // Define your page size
$userId = 1; // The user whose posts we are fetching

do {
    /** @var DynamoBreezeResult $result */
    $result = DynamoBreeze::withTableIdentifier(self::TABLE_IDENTIFIER)
        ->accessPattern('FetchUserPosts', ['user_id' => $userId]) // Specify access pattern and relevant data
        ->limit($pageSize) // Limit the number of items fetched per request
        ->exclusiveStartKey($startKey) // Identify the starting point for the next set of results
        ->get(); // Execute the query

    $items = $result->getItems(); // Retrieve the items from the current page
    $retrievedItemsCount += $result->getCount(); // Increment the count

    // Check if there are more pages of results
    $startKey = $result->getLastEvaluatedKey();
} while ($startKey !== null);

// At this point, $retrievedItemsCount contains the total count of items retrieved
// And $items contains the items from the last fetched page
```

## Batch Retrieval with batchGet

When working with DynamoDB, there may be situations where you need to retrieve multiple items by their primary keys. The `batchGet` method provided by DynamoBreeze allows for the retrieval of multiple items across one or more tables in a single operation, which can be a more efficient alternative to issuing multiple `GetItem` requests.

In the following example, we are retrieving posts from multiple users stored in two different tables. We use batchGet to retrieve items from both the SocialMediaTable and ExampleTable using their primary keys.

```php
// Define the keys for the items we want to retrieve.
$firstTableKeysToGet = [
    ['PK' => 'USER#1', 'SK' => 'POST#123'],
    ['PK' => 'USER#1', 'SK' => 'POST#124'],
    ['PK' => 'USER#2', 'SK' => 'POST#123'],
    ['PK' => 'USER#3', 'SK' => 'POST#1'],
];

$secondTableKeysToGet = [
    ['PostId' => '1', 'Timestamp' => 11111],
];

$result = DynamoBreeze::batchGet([
        'first_table_identifier' => [
            'keys' => $firstTableKeysToGet,
        ],
        'second_table_identifier' => [
            'keys' => $secondTableKeysToGet,
        ],
    ]);
```

## Testing

`composer test`

## Contribution

Contributions are welcome and will be fully credited. Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## License

DynamoBreeze is open-sourced software licensed under the [MIT license](https://github.com/musonza/dynamo-breeze/blob/main/LICENSE).

## Contact

For general questions, brainstorming, and open-ended discussion, please use our [GitHub Discussions](https://github.com/musonza/dynamo-breeze/discussions/2). This is a great place to start socializing ideas, seek help from other community members, or discuss broader topics related to the project. Remember, a fresh perspective can be invaluable, and your insights might just spark the next big feature or improvement.
