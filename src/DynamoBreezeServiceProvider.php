<?php

namespace Musonza\DynamoBreeze;

use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Marshaler;
use Illuminate\Support\ServiceProvider;

class DynamoBreezeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->publishes([
            __DIR__.'/../config' => config_path(),
        ], 'dynamo-breeze');

        $this->app->singleton(DynamoDbClient::class, function (): DynamoDbClient {
            return new DynamoDbClient([
                'version' => 'latest',
                'region' => Configuration::getRegion(),
                'endpoint' => Configuration::getDynamodbEndpoint(),
            ]);
        });

        $this->app->bind(DynamoBreezeService::class, function ($app) {
            return new DynamoBreezeService(
                $app->make(DynamoDbClient::class),
                config('dynamo-breeze'),
                new Marshaler()
            );
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config' => config_path('dynamo-breeze.php'),
        ], 'dynamo-breeze');
    }
}
