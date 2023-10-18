<?php

namespace Musonza\DynamoBreeze;

use Aws\Result;

class DynamoBreezeResult
{
    protected Result $awsResult;

    public function __construct(Result $awsResult)
    {
        $this->awsResult = $awsResult;
    }

    public function getItems(): ?array
    {
        return $this->awsResult->get('Items');
    }

    public function getCount(): ?int
    {
        return $this->awsResult->get('Count');
    }

    public function getLastEvaluatedKey()
    {
        return $this->awsResult->get('LastEvaluatedKey');
    }

    public function getRawResult(): Result
    {
        return $this->awsResult;
    }
}
