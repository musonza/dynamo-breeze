<?php

namespace Musonza\DynamoBreeze;

use Aws\DynamoDb\DynamoDbClient;

class DynamoDbClientFactory
{
    protected array $configurations;

    public function __construct(array $configurations)
    {
        $this->configurations = $configurations;
    }

    public function make(string $table): DynamoDbClient
    {
        $config = $this->configurations['tables'][$table]['credentials'] ?? 'default';
        $credentials = $this->configurations['credentials'][$config];
        $args = [
            'region' => $credentials['region'],
            'version' => $credentials['version'],
            'endpoint' => $credentials['endpoint'] ?? null,
        ];

        if (isset($credentials['credentials']['key']) && isset($credentials['credentials']['secret'])) {
            $args['credentials'] = [
                'key' => $credentials['credentials']['key'],
                'secret' => $credentials['credentials']['secret'],
            ];
        }

        return new DynamoDbClient($args);
    }
}
