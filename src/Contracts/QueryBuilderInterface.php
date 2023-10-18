<?php

namespace Musonza\DynamoBreeze\Contracts;

interface QueryBuilderInterface
{
    public function buildQuery(array $patternConfig): array;

    public function setTableName(string $tableName): void;

    public function getTableName(): ?string;

    public function getExpressions(): ?array;

    public function setAccessPatternConfig(?array $config): void;

    public function getAccessPatternConfig(): ?array;

    public function getAccessPatternName(): ?string;
}
