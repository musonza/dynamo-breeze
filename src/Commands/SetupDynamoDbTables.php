<?php

namespace Musonza\DynamoBreeze\Commands;

use Aws\DynamoDb\DynamoDbClient;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class SetupDynamoDbTables extends Command
{
    protected $signature = 'dynamo-breeze:setup-tables';

    protected $description = 'Set up DynamoDB tables based on the package configuration';

    protected DynamoDbClient $dynamoDb;

    public function handle(DynamoDbClient $dynamoDb, bool $create = true): int
    {
        $this->dynamoDb = $dynamoDb;
        $tablesConfig = config('dynamo-breeze.tables');

        foreach ($tablesConfig as $tableConfig) {
            if ($create) {
                $this->createTable($tableConfig);
            } else {
                $this->deleteTable($tableConfig);
            }
        }

        return 1;
    }

    public function deleteTable(array $tableConfig): void
    {
        $this->dynamoDb->deleteTable([
            'TableName' => $tableConfig['table_name'],
        ]);
    }

    protected function createTable(array $tableConfig): void
    {
        $args = [
            'TableName' => $tableConfig['table_name'],
            'AttributeDefinitions' => $this->getAttributeDefinitions($tableConfig['attributes'], $tableConfig['global_secondary_indexes'] ?? []),
            'KeySchema' => $this->getKeySchema($tableConfig['partition_key'], $tableConfig['sort_key'] ?? null),
            'ProvisionedThroughput' => [
                'ReadCapacityUnits' => $tableConfig['read_capacity_units'] ?? 5,
                'WriteCapacityUnits' => $tableConfig['write_capacity_units'] ?? 5,
            ],
        ];

        $gsis = $this->transformGlobalSecondaryIndexes($tableConfig['global_secondary_indexes'] ?? []);

        if (! empty($gsis)) {
            $args['GlobalSecondaryIndexes'] = $gsis['GlobalSecondaryIndexes'];
        }

        $this->dynamoDb->createTable($args);

        $this->dynamoDb->waitUntil('TableExists', [
            'TableName' => $tableConfig['table_name'],
        ]);
    }

    public function transformGlobalSecondaryIndexes(array $gsiConfig): array
    {
        if (empty($gsiConfig)) {
            return [];
        }

        $transformedGSIs = [];
        $gsiAttributeDefinitions = [];

        foreach ($gsiConfig as $gsi) {
            $transformedGSIs[] = [
                'IndexName' => $gsi['index_name'],
                'KeySchema' => $this->getKeySchema(
                    $gsi['key_schema']['partition_key'],
                    $gsi['key_schema']['sort_key']
                ),
                // Assuming all attributes are to be included in the GSI
                'Projection' => [
                    'ProjectionType' => 'ALL',
                ],
                // Assuming a provisioned throughput of 5 read and 5 write capacity units for each GSI
                'ProvisionedThroughput' => [
                    'ReadCapacityUnits' => 5,
                    'WriteCapacityUnits' => 5,
                ],
            ];

            foreach ($gsi['attributes'] as $attribute => $type) {
                $gsiAttributeDefinitions[] = [
                    'AttributeName' => $attribute,
                    'AttributeType' => $type,
                ];
            }
        }

        return [
            'GlobalSecondaryIndexes' => $transformedGSIs,
            'GSIAttributeDefinitions' => $gsiAttributeDefinitions,
        ];
    }

    protected function getAttributeDefinitions(array $attributes, array $globalSecondaryIndexes = []): array
    {
        $gsiAttributes = Collection::make($globalSecondaryIndexes)->reduce(function ($carry, $gsi) {
            foreach ($gsi['key_schema'] as $attributeName) {
                // TODO Assuming all GSI attributes are of type 'S' (string)
                $carry[$attributeName] = 'S';
            }

            return $carry;
        }, []);

        // Merge main attributes and GSI attributes
        $allAttributes = array_merge($attributes, $gsiAttributes);

        return Collection::make($allAttributes)->map(function ($type, $attribute) {
            return [
                'AttributeName' => $attribute,
                'AttributeType' => $type,
            ];
        })->values()->all();
    }

    protected function getKeySchema(string $partitionKey, ?string $sortKey): array
    {
        $keySchema = [
            [
                'AttributeName' => $partitionKey,
                'KeyType' => 'HASH',
            ],
        ];

        if ($sortKey) {
            $keySchema[] = [
                'AttributeName' => $sortKey,
                'KeyType' => 'RANGE',
            ];
        }

        return $keySchema;
    }
}
