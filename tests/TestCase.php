<?php

namespace Musonza\DynamoBreeze\Tests;

use Exception;
use Illuminate\Foundation\Application;
use Musonza\DynamoBreeze\DynamoBreezeServiceProvider;
use Musonza\DynamoBreeze\Facades\DynamoBreeze;

class TestCase extends \Orchestra\Testbench\TestCase
{
    public function tearDown(): void
    {
        $this->checkEnvironment();
        parent::tearDown();
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->checkEnvironment();
    }

    private function checkEnvironment()
    {
        if (! app()->environment('testing')) {
            throw new Exception('You can only run these tests in a testing environment');
        }
    }

    protected function getPackageProviders($app): array
    {
        return [
            DynamoBreezeServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'DynamoBreeze' => DynamoBreeze::class,
        ];
    }

    /**
     * Define environment setup.
     *
     * @param  Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);
        $app['config']->set('dynamo-breeze', require 'config/config.php');
    }
}
