<?php

namespace Musonza\DynamoBreeze\Facades;

use Illuminate\Support\Facades\Facade;
use Musonza\DynamoBreeze\DynamoBreezeService;

class DynamoBreeze extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return DynamoBreezeService::class;
    }
}
