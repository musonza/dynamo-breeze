<?php

namespace Musonza\DynamoBreeze\Tests\Unit;

use Musonza\DynamoBreeze\DynamoBreezeService;
use Musonza\DynamoBreeze\Tests\TestCase;

class DynamoBreezeServiceTest extends TestCase
{
    private $dynamoDb;

    private DynamoBreezeService $dynamoBreezeService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dynamoBreezeService = app(DynamoBreezeService::class);
    }

    public function testTableMethodSetsTableName(): void
    {
        $this->dynamoBreezeService->table('example_table');
        $this->assertSame('ExampleTable', $this->dynamoBreezeService->getTableName());
    }

    /**
     * @dataProvider provideAccessPatternData
     */
    public function testAccessPatternMethodReplacesAndMarshalsPlaceholders(
        string $table,
        string $patternName,
        array $dataProvider,
        array $expectedExpressions
    ): void {
        $this->dynamoBreezeService->table($table)
            ->accessPattern($patternName, $dataProvider);
        $accessPatternConfig = $this->dynamoBreezeService->getAccessPatternConfig();

        $this->assertSame($patternName, $accessPatternConfig['patternName']);
        $this->assertSame($expectedExpressions, $this->dynamoBreezeService->getExpressions());
    }

    public function provideAccessPatternData(): array
    {
        return [
            'FetchUserPosts Example' => [
                'table' => 'example_table',
                'patternName' => 'FetchUserPosts',
                'dataProvider' => ['user_id' => 123, 'timestamp' => 123456],
                'expectedExpressions' => [':pk_val' => ['S' => 'USER#123'], ':sk_val' => ['N' => '123456']],
            ],
            'FetchPostComments Example' => [
                'table' => 'social_media',
                'patternName' => 'FetchPostComments',
                'dataProvider' => ['post_id' => 'POST1'],
                'expectedExpressions' => [':pk_val' => ['S' => 'POST#POST1'], ':sk_prefix_val' => ['S' => 'COMMENT#']],
            ],
            'ExampleWithFilterExpression' => [
                'table' => 'example_table',
                'patternName' => 'ExampleAccessPatternWithFilter',
                'dataProvider' => ['user_id' => 1, 'age' => 30],
                'expectedExpressions' => [':pk_val' => ['S' => 'USER#1'], ':minAge' => ['N' => '30']],
            ],
        ];
    }
}
