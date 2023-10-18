<?php

namespace Musonza\DynamoBreeze;

use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Marshaler;

class DynamoBreezeService
{
    protected DynamoDbClient $dynamoDb;

    protected array $config;

    protected string $tableName;

    protected string $tableIdentifier;

    protected array $accessPatternConfig = [];

    protected Marshaler $marshaler;

    public function __construct(DynamoDbClient $dynamoDb, array $config, Marshaler $marshaler)
    {
        $this->dynamoDb = $dynamoDb;
        $this->config = $config;
        $this->marshaler = $marshaler;
    }

    public function getClient(): DynamoDbClient
    {
        return $this->dynamoDb;
    }

    public function table(string $tableIdentifier): self
    {
        $this->tableIdentifier = $tableIdentifier;

        if (! isset($this->config['tables'][$tableIdentifier])) {
            throw new \Exception("Table identifier {$tableIdentifier} is not defined in config");
        }

        $this->tableName = $this->config['tables'][$tableIdentifier]['table_name'];

        return $this;
    }

    public function getTableName(): string
    {
        return $this->tableName;
    }

    public function accessPattern(string $patternName, array $dataProvider): self
    {
        $this->accessPatternConfig['patternName'] = $patternName;
        $accessPattern = $this->config['tables'][$this->tableIdentifier]['access_patterns'][$patternName];

        if (isset($accessPattern['expression_attribute_values'])) {
            $expressionAttributes = $this->replacePlaceholders($accessPattern['expression_attribute_values'], $dataProvider);
            $marshaledAttributes = $this->marshalExpressionAttributeValues($expressionAttributes);
            $this->accessPatternConfig['expressions'] = $marshaledAttributes;
        }

        return $this;
    }

    public function getAccessPatternConfig(): array
    {
        return $this->accessPatternConfig;
    }

    public function getAccessPatternName(): ?string
    {
        return $this->accessPatternConfig['patternName'] ?? null;
    }

    public function getExpressions(): ?array
    {
        return $this->accessPatternConfig['expressions'] ?? null;
    }

    public function replacePlaceholders(array $expressionAttributes, array $dataProvider): array
    {
        foreach ($expressionAttributes as &$attribute) {
            foreach ($attribute as &$value) {
                foreach ($dataProvider as $placeholder => $replacement) {
                    $value = str_replace("<$placeholder>", $replacement, $value);
                }
            }
        }

        return $expressionAttributes;
    }

    public function marshalExpressionAttributeValues(array $expressionAttributes): array
    {
        foreach ($expressionAttributes as $key => $attribute) {
            foreach ($attribute as $type => $value) {
                // Ensure the value is in the correct format and not already marshaled
                if ($type === 'S' && is_string($value)) {
                    $expressionAttributes[$key] = [$type => $value];
                } elseif ($type === 'N' && is_numeric($value)) {
                    $expressionAttributes[$key] = [$type => (string) $value];
                } else {
                    // TODO test Maps etc
                    $expressionAttributes[$key] = $this->marshaler->marshalValue($value);
                }
            }
        }

        return $expressionAttributes;
    }

    public function get(): DynamoBreezeResult
    {
        $patternConfig = $this->config['tables'][$this->tableIdentifier]['access_patterns'][$this->getAccessPatternName()];

        $query = [
            'TableName' => $this->tableName,
        ];

        if (isset($patternConfig['key_condition_expression'])) {
            $query['KeyConditionExpression'] = $patternConfig['key_condition_expression'];
        }

        if (isset($patternConfig['expression_attribute_names'])) {
            $query['ExpressionAttributeNames'] = $patternConfig['expression_attribute_names'];
        }

        if ($this->getExpressions()) {
            $query['ExpressionAttributeValues'] = $this->getExpressions();
        }

        if (isset($patternConfig['gsi_name'])) {
            $query['IndexName'] = $patternConfig['gsi_name'];
        }

        if (isset($patternConfig['projection_expression'])) {
            $query['ProjectionExpression'] = $patternConfig['projection_expression'];
        }

        if (isset($patternConfig['scan_index_forward'])) {
            $query['ScanIndexForward'] = $patternConfig['scan_index_forward'];
        }

        if (isset($patternConfig['limit'])) {
            $query['Limit'] = $patternConfig['limit'];
        }

        $result = $this->getClient()->query($query);

        return new DynamoBreezeResult($result);
    }

    /**
     * Retrieve records with conditions from DynamoDB.
     *
     * @return DynamoBreezeResult
     */
    public function retrieveRecordsWithConditions(array $parameters)
    {
        $awsResult = $this->dynamoDb->query($parameters);

        return new DynamoBreezeResult($awsResult);
    }

    /**
     * Insert a record into DynamoDB.
     *
     * @return mixed
     */
    public function insertRecord(array $parameters)
    {
        return $this->dynamoDb->putItem($parameters);
    }

    /**
     * Update a record in DynamoDB.
     *
     * @return mixed
     */
    public function updateRecord(array $parameters)
    {
        return $this->dynamoDb->updateItem($parameters);
    }

    /**
     * Delete a record from DynamoDB.
     *
     * @return mixed
     */
    public function deleteRecord(array $parameters)
    {
        return $this->dynamoDb->deleteItem($parameters);
    }
}
