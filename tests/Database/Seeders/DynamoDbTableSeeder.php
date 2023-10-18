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

    public function seed(array $items, string $tableIdentifier): void
    {
        // Specify the table we're working with.
        $this->service->withTableIdentifier($tableIdentifier);
        foreach ($items as $item) {
            $this->putItem($item);
        }
    }

    protected function putItem(array $item): void
    {
        $dynamoDbItem = $this->formatItemForDynamoDb($item);
        $this->service->insertRecord(['Item' => $dynamoDbItem]);
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
