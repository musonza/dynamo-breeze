<?php

namespace Musonza\DynamoBreeze;

use Musonza\DynamoBreeze\Contracts\QueryBuilderInterface;

class DefaultQueryBuilder implements QueryBuilderInterface
{
    protected string $tableName;

    protected ?array $expressions = null;

    protected array $accessPatternConfig = [];

    public function buildQuery(array $patternConfig): array
    {
        $query = ['TableName' => $this->tableName];

        $defaultConfigToQueryMap = [
            'key_condition_expression' => 'KeyConditionExpression',
            'filter_expression' => 'FilterExpression',
            'index_name' => 'IndexName',
            'projection_expression' => 'ProjectionExpression',
            'scan_index_forward' => 'ScanIndexForward',
            'consistent_read' => 'ConsistentRead',
            'returned_consumed_capacity' => 'ReturnConsumedCapacity',
            'limit' => 'Limit',
        ];

        // Retrieve any additional config-to-query mappings from the application configuration.
        $additionalConfigMappings = config('dynamo-breeze.additional_query_mappings');

        // If there are any additional mappings, merge them with the default ones.
        if (is_array($additionalConfigMappings)) {
            $configToQueryMap = array_merge($defaultConfigToQueryMap, $additionalConfigMappings);
        } else {
            $configToQueryMap = $defaultConfigToQueryMap;
        }

        foreach ($configToQueryMap as $configKey => $queryKey) {
            if (isset($patternConfig[$configKey])) {
                $query[$queryKey] = $patternConfig[$configKey];
            }
        }

        if ($expressions = $this->getExpressions()) {
            $query['ExpressionAttributeValues'] = $expressions;
        }

        return $query;
    }

    public function setTableName(string $tableName): void
    {
        $this->tableName = $tableName;
    }

    public function getTableName(): ?string
    {
        return $this->tableName;
    }

    public function getExpressions(): ?array
    {
        return $this->accessPatternConfig['expressions'] ?? null;
    }

    public function setAccessPatternConfig(?array $config): void
    {
        $this->accessPatternConfig = $config;
    }

    public function getAccessPatternConfig(): ?array
    {
        return $this->accessPatternConfig;
    }

    public function getAccessPatternName(): ?string
    {
        return $this->accessPatternConfig['patternName'] ?? null;
    }
}
