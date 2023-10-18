<?php

namespace Musonza\DynamoBreeze\Tests;

use Aws\DynamoDb\DynamoDbClient;
use Carbon\Carbon;
use Musonza\DynamoBreeze\Commands\SetupDynamoDbTables;
use Musonza\DynamoBreeze\Tests\Database\Seeders\DynamoDbTableSeeder;
use Tuupola\KsuidFactory;

class FeatureTestCase extends TestCase
{
    protected DynamoDbClient $client;

    public function setUp(): void
    {
        parent::setUp();
        $this->client = app(DynamoDbClient::class);
        $this->app->make(SetupDynamoDbTables::class)->handle($this->client);
    }

    public function tearDown(): void
    {
        $this->app->make(SetupDynamoDbTables::class)->handle($this->client, false);
        parent::tearDown();
    }

    public function seedDynamoDbTable(array $data, string $tableName): void
    {
        $seeder = app(DynamoDbTableSeeder::class);
        $seeder->seed($data, $tableName);
    }

    public function generateKSUID(Carbon $date): string
    {
        return KsuidFactory::fromTimestamp($date->getTimestamp())->string();
    }
}
