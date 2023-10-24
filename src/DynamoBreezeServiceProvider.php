<?php

namespace Musonza\DynamoBreeze;

use Illuminate\Support\ServiceProvider;
use Musonza\DynamoBreeze\Commands\DynamoAccessPatterns;
use Musonza\DynamoBreeze\Commands\SetupDynamoDbTables;
use Musonza\DynamoBreeze\Contracts\QueryBuilderInterface;

class DynamoBreezeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(DynamoDbClientFactory::class, function ($app) {
            $config = $app->make('config')->get('dynamo-breeze');

            return new DynamoDbClientFactory($config);
        });

        $this->app->bind(QueryBuilderInterface::class, DefaultQueryBuilder::class);

        $this->app->bind(DynamoBreezeService::class, function ($app) {
            return new DynamoBreezeService(
                $app->make(QueryBuilderInterface::class),
                $app->make(DynamoDbClientFactory::class),
                config('dynamo-breeze'),
                $app->make(ExpressionAttributeHandler::class),
            );
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/dynamo-breeze.php' => config_path('dynamo-breeze.php'),
        ], 'dynamo-breeze');

        if ($this->app->runningInConsole()) {
            $this->commands([
                SetupDynamoDbTables::class,
                DynamoAccessPatterns::class,
            ]);
        }
    }
}
