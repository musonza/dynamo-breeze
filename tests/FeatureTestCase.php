<?php

namespace Musonza\DynamoBreeze\Tests;

use Musonza\DynamoBreeze\Commands\SetupDynamoDbTables;
use Musonza\DynamoBreeze\Tests\Database\Seeders\DynamoDbTableSeeder;

class FeatureTestCase extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->app->make(SetupDynamoDbTables::class)->handle();
    }

    public function tearDown(): void
    {
        $this->app->make(SetupDynamoDbTables::class)->handle(false);
        parent::tearDown();
    }

    public function seedDynamoDbTable(array $data, string $tableIdentifier): void
    {
        $seeder = app(DynamoDbTableSeeder::class);
        $seeder->seed($data, $tableIdentifier);
    }
}
