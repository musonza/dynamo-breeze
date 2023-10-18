<?php

namespace Musonza\DynamoBreeze\Tests\Database\Seeders;

use Aws\DynamoDb\Marshaler;
use Musonza\DynamoBreeze\DynamoBreezeService;

class DynamoDbTableSeeder
{
    protected DynamoBreezeService $service;

    public function __construct()
    {
        $this->service = app(DynamoBreezeService::class);
    }

    public function seed(array $items, string $tableName): void
    {
        foreach ($items as $item) {
            $this->putItem($item, $tableName);
        }
    }

    protected function putItem(array $item, string $tableName): void
    {
        $dynamoDbItem = $this->formatItemForDynamoDb($item);

        $params = [
            'TableName' => $tableName,
            'Item' => $dynamoDbItem,
        ];

        $this->service->getClient()->putItem($params);
    }

    protected function formatItemForDynamoDb(array $item): array
    {
        $formattedItem = [];

        $marshaler = new Marshaler();

        foreach ($item as $key => $value) {
            $formattedItem[$key] = $marshaler->marshalValue($value);
        }

        return $formattedItem;
    }
}
