<?php

namespace Musonza\DynamoBreeze;

class Configuration
{
    public static function getDynamodbEndpoint(): string
    {
        // multiple endpoints?
        return config('dynamo-breeze.sdk.endpoint', env('DYNAMODB_ENDPOINT'));
    }

    public static function getRegion(): string
    {
        return config('dynamo-breeze.sdk.region', env('AWS_REGION'));
    }
}
