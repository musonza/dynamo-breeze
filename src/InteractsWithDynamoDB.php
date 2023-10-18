<?php

namespace Musonza\DynamoBreeze;

trait InteractsWithDynamoDB
{
    protected $dynamoBreezeService = null;

    /**
     * Get an instance of the DynamoBreezeService.
     */
    protected function getDynamoBreezeService(): DynamoBreezeService
    {
        if (is_null($this->dynamoBreezeService)) {
            $this->dynamoBreezeService = app(DynamoBreezeService::class);
        }

        return $this->dynamoBreezeService;
    }

    /**
     * Ensure the using class has defined `getTable` method.
     */
    protected function ensureTableMethodExists()
    {
        if (! method_exists($this, 'getTable')) {
            throw new \RuntimeException(
                sprintf('You must define a `getTable` method in %s to use the InteractsWithDynamoDB trait.', get_class($this))
            );
        }
    }

    /**
     * Fetch data based on provided parameters.
     *
     * @return mixed
     */
    public function fetchData(array $parameters)
    {
        $this->ensureTableMethodExists();

        return $this->getDynamoBreezeService()->retrieveRecordsWithConditions($parameters);
    }

    /**
     * Example method to retrieve data using a defined access pattern.
     *
     * @return mixed
     */
    public function getByAccessPattern(string $patternName, array $keyConditions)
    {
        $this->ensureTableMethodExists();
        $config = config('dynamo-breeze.tables.'.$this->getTable());

        $pattern = $config['access_patterns'][$patternName] ?? null;

        if (! $pattern) {
            throw new \InvalidArgumentException("Access pattern [$patternName] is not defined.");
        }

        $parameters = [
            'TableName' => $config['table_name'],
            'KeyConditionExpression' => $pattern['key_condition_expression'],
            'ExpressionAttributeValues' => $this->prepareExpressionAttributeValues($keyConditions),
        ];

        if (isset($pattern['gsi_name'])) {
            $parameters['IndexName'] = $pattern['gsi_name'];
        }

        return $this->fetchData($parameters);
    }

    /**
     * Prepare the ExpressionAttributeValues parameter for DynamoDB.
     */
    private function prepareExpressionAttributeValues(array $keyConditions): array
    {
        $expressionAttributeValues = [];

        foreach ($keyConditions as $key => $value) {
            $expressionAttributeValues[":{$key}_val"] = $value;
        }

        return $expressionAttributeValues;
    }
}
