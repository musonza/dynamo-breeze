<?php

namespace Musonza\DynamoBreeze;

use Aws\DynamoDb\DynamoDbClient;
use Musonza\DynamoBreeze\Contracts\QueryBuilderInterface;

class DynamoBreezeService
{
    protected ?DynamoDbClient $dynamoDb = null;

    protected DynamoDbClientFactory $dynamoDbFactory;

    protected array $config;

    protected string $tableIdentifier;

    public QueryBuilderInterface $queryBuilder;

    protected ExpressionAttributeHandler $expressionAttributeHandler;

    protected $exclusiveStartKey = null;

    protected int $limit = 0;

    protected ?string $projectionExpression = null;

    protected ?string $returnConsumedCapacity = null;

    public function __construct(
        QueryBuilderInterface $queryBuilder,
        DynamoDbClientFactory $dynamoDbFactory,
        array $config,
        ExpressionAttributeHandler $expressionAttributeHandler
    ) {
        $this->queryBuilder = $queryBuilder;
        $this->dynamoDbFactory = $dynamoDbFactory;
        $this->config = $config;
        $this->expressionAttributeHandler = $expressionAttributeHandler;
    }

    public function withTableIdentifier(string $tableIdentifier): self
    {
        $this->tableIdentifier = $tableIdentifier;

        if (! isset($this->config['tables'][$tableIdentifier])) {
            throw new \Exception("Table identifier {$tableIdentifier} is not defined in config");
        }

        $tableName = $this->config['tables'][$tableIdentifier]['table_name'];

        $this->queryBuilder->setTableName($tableName);

        $this->dynamoDb = $this->dynamoDbFactory->make($tableIdentifier);

        return $this;
    }

    public function limit(int $limit): self
    {
        $this->limit = $limit;

        return $this;
    }

    public function getDynamoDbClient(): DynamoDbClient
    {
        if (! $this->dynamoDb) {
            $this->dynamoDb = $this->dynamoDbFactory->make($this->tableIdentifier);
        }

        return $this->dynamoDb;
    }

    public function exclusiveStartKey($startKey): self
    {
        $this->exclusiveStartKey = $startKey;

        return $this;
    }

    public function projectionExpression(string $expression): self
    {
        $this->projectionExpression = $expression;

        return $this;
    }

    public function returnConsumedCapacity(string $returnConsumedCapacity): self
    {
        $this->returnConsumedCapacity = $returnConsumedCapacity;

        return $this;
    }

    public function accessPattern(string $patternName, array $dataProvider): self
    {
        $accessPatternConfig = [];
        $accessPatternConfig['patternName'] = $patternName;
        $accessPattern = $this->config['tables'][$this->tableIdentifier]['access_patterns'][$patternName];

        if (isset($accessPattern['expression_attribute_values'])) {
            $accessPatternConfig['expressions'] = $this->expressionAttributeHandler->prepareExpressionAttributes(
                $accessPattern['expression_attribute_values'],
                $dataProvider
            );
        }

        $this->queryBuilder->setAccessPatternConfig($accessPatternConfig);

        return $this;
    }

    /* Insert a record into DynamoDB.
    *
    * @return mixed
    */
    public function insertRecord(array $parameters)
    {
        $parameters = array_merge(['TableName' => $this->queryBuilder->getTableName()], $parameters);

        return $this->getDynamoDbClient()->putItem($parameters);
    }

    public function get(): DynamoBreezeResult
    {
        $patternConfig = $this->config['tables'][$this->tableIdentifier]['access_patterns'][$this->queryBuilder->getAccessPatternName()];

        $query = $this->queryBuilder->buildQuery($patternConfig);

        $this->queryBuilder->setTableName($this->queryBuilder->getTableName());

        if ($this->limit) {
            $query['Limit'] = $this->limit;
        }

        if ($this->exclusiveStartKey) {
            $query['ExclusiveStartKey'] = $this->exclusiveStartKey;
        }

        if ($this->projectionExpression) {
            $query['ProjectionExpression'] = $this->projectionExpression;
        }

        if ($this->returnConsumedCapacity) {
            $query['ReturnConsumedCapacity'] = $this->returnConsumedCapacity;
        }

        $result = $this->getDynamoDbClient()->query($query);

        return new DynamoBreezeResult($result);
    }

    public function batchGet(array $batchItems): DynamoBreezeResult
    {
        $requestItems = [];

        foreach ($batchItems as $tableIdentifier => $details) {
            $this->tableIdentifier = $tableIdentifier;
            $keys = [];
            foreach ($details['keys'] as $keyData) {
                $keys[] = $this->expressionAttributeHandler->marshaler->marshalItem($keyData);
            }

            $requestItems[$this->config['tables'][$tableIdentifier]['table_name']] = [
                'Keys' => $keys,
                'ProjectionExpression' => $this->projectionExpression ?? null,
            ];
        }

        $batchGetParams = ['RequestItems' => $requestItems];

        if ($this->returnConsumedCapacity) {
            $batchGetParams['ReturnConsumedCapacity'] = $this->returnConsumedCapacity;
        }

        $result = $this->getDynamoDbClient()->batchGetItem($batchGetParams);

        return new DynamoBreezeResult($result);
    }

    /**
     * Insert a record into DynamoDB.
     *
     * @return mixed
     * @return mixed
     */
    public function updateRecord(array $parameters)
    {
        return $this->getDynamoDbClient()->updateItem($parameters);
    }

    /**
     * Delete a record from DynamoDB.
     *
     * @return mixed
     */
    public function deleteRecord(array $parameters)
    {
        return $this->getDynamoDbClient()->deleteItem($parameters);
    }
}
