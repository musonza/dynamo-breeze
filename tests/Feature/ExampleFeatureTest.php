<?php

namespace Musonza\DynamoBreeze\Tests\Feature;

use Carbon\Carbon;
use Musonza\DynamoBreeze\DynamoDbConstants;
use Musonza\DynamoBreeze\Facades\DynamoBreeze;
use Musonza\DynamoBreeze\Tests\FeatureTestCase;
use Musonza\DynamoBreeze\Tests\Traits\Helpers;

class ExampleFeatureTest extends FeatureTestCase
{
    use Helpers;

    private const TABLE_NAME = 'SocialMediaTable';

    private const TABLE_IDENTIFIER = 'social_media';

    /**
     * @dataProvider tableCreationProvider
     */
    public function testCreatesTablesFromConfiguration($data)
    {
        $this->assertTableExists(self::TABLE_IDENTIFIER, self::TABLE_NAME, $data);
        $this->assertTrue(true);
    }

    public function tableCreationProvider()
    {
        return [
            [
                [
                    'AttributeDefinitions' => [
                        [
                            'AttributeName' => 'PK',
                            'AttributeType' => DynamoDbConstants::ATTRIBUTE_TYPE_STRING,
                        ],
                        [
                            'AttributeName' => 'SK',
                            'AttributeType' => DynamoDbConstants::ATTRIBUTE_TYPE_STRING,
                        ],
                        [
                            'AttributeName' => 'GSI1PK',
                            'AttributeType' => DynamoDbConstants::ATTRIBUTE_TYPE_STRING,
                        ],
                        [
                            'AttributeName' => 'GSI1SK',
                            'AttributeType' => DynamoDbConstants::ATTRIBUTE_TYPE_STRING,
                        ],
                        [
                            'AttributeName' => 'GSI2PK',
                            'AttributeType' => DynamoDbConstants::ATTRIBUTE_TYPE_STRING,
                        ],
                        [
                            'AttributeName' => 'GSI2SK',
                            'AttributeType' => DynamoDbConstants::ATTRIBUTE_TYPE_NUMBER,
                        ],
                    ],
                    'GlobalSecondaryIndexes' => [
                        [
                            'IndexName' => 'GSI1',
                            'KeySchema' => [
                                [
                                    'AttributeName' => 'GSI1PK',
                                    'KeyType' => DynamoDbConstants::KEY_TYPE_HASH,
                                ],
                                [
                                    'AttributeName' => 'GSI1SK',
                                    'KeyType' => DynamoDbConstants::KEY_TYPE_RANGE,
                                ],
                            ],
                            'Projection' => [
                                'ProjectionType' => 'ALL',
                            ],
                            'IndexStatus' => 'ACTIVE',
                            'ProvisionedThroughput' => [
                                'ReadCapacityUnits' => 5,
                                'WriteCapacityUnits' => 5,
                            ],
                            'IndexSizeBytes' => 0,
                        ],
                        [
                            'IndexName' => 'GSI2',
                            'KeySchema' => [
                                [
                                    'AttributeName' => 'GSI2PK',
                                    'KeyType' => DynamoDbConstants::KEY_TYPE_HASH,
                                ],
                                [
                                    'AttributeName' => 'GSI2SK',
                                    'KeyType' => DynamoDbConstants::KEY_TYPE_RANGE,
                                ],
                            ],
                            'Projection' => [
                                'ProjectionType' => 'KEYS_ONLY',
                            ],
                            'IndexStatus' => 'ACTIVE',
                            'ProvisionedThroughput' => [
                                'ReadCapacityUnits' => 10,
                                'WriteCapacityUnits' => 5,
                            ],
                            'IndexSizeBytes' => 0,
                        ],
                    ],
                ],
            ],
        ];
    }

    public function testFetchUserPosts(): void
    {
        $posts = [
            ['PK' => 'USER#1', 'SK' => 'POST#123', 'CategoryId' => 'A', 'Content' => 'Hello, World!', 'Timestamp' => time()],
            ['PK' => 'USER#1', 'SK' => 'POST#124', 'CategoryId' => 'B', 'Content' => 'My second post!', 'Timestamp' => time()],
        ];

        $this->seedDynamoDbTable($posts, self::TABLE_IDENTIFIER);
        $this->assertEquals(2, $this->fetchUserPosts(1)->getCount());
    }

    public function testBatchGetUserPosts(): void
    {
        $posts = [
            ['PK' => 'USER#1', 'SK' => 'POST#123', 'CategoryId' => 'A', 'Content' => 'Hello, World!', 'Timestamp' => time()],
            ['PK' => 'USER#1', 'SK' => 'POST#124', 'CategoryId' => 'B', 'Content' => 'My second post!', 'Timestamp' => time()],
            ['PK' => 'USER#2', 'SK' => 'POST#123', 'CategoryId' => 'A', 'Content' => 'Hello, World!', 'Timestamp' => time()],
            ['PK' => 'USER#3', 'SK' => 'POST#1', 'CategoryId' => 'B', 'Content' => 'My second post!', 'Timestamp' => time()],
        ];
        $this->seedDynamoDbTable($posts, self::TABLE_IDENTIFIER);

        $secondTableIdentifier = 'example_table';
        $secondTableData = ['PostId' => '1', 'Timestamp' => 11111];
        $this->seedDynamoDbTable([$secondTableData], $secondTableIdentifier);

        // Define the keys for the items we want to retrieve.
        $keysToGet = [
            ['PK' => 'USER#1', 'SK' => 'POST#123'],
            ['PK' => 'USER#1', 'SK' => 'POST#124'],
            ['PK' => 'USER#2', 'SK' => 'POST#123'],
            ['PK' => 'USER#3', 'SK' => 'POST#1'],
            ['PK' => 'USER#404', 'SK' => 'POST#1'],
        ];

        $secondTableKeysToGet = [
            ['PostId' => '1', 'Timestamp' => 11111],
        ];

        $result = DynamoBreeze::withTableIdentifier(self::TABLE_IDENTIFIER)
            ->batchGet([
                self::TABLE_IDENTIFIER => [
                    'keys' => $keysToGet,
                ],
                'example_table' => [
                    'keys' => $secondTableKeysToGet,
                ],
            ]);

        $retrievedPosts = $result->getRawResult()->get('Responses')['SocialMediaTable'];

        $this->assertCount(4, $retrievedPosts);

        $retrievedFromSecondTable = $result->getRawResult()->get('Responses')['ExampleTable'];

        $this->assertCount(1, $retrievedFromSecondTable);
    }

    public function testQueryPagination(): void
    {
        // More than 10 items for USER#1.
        $totalItems = 20;
        $pageSize = 10;
        $userId = 1;

        $items = [];
        for ($i = 1; $i <= $totalItems; $i++) {
            $items[] = [
                'PK' => 'USER#1',
                'SK' => 'POST#'.$i,
                'Content' => 'Content '.$i,
                'Timestamp' => time(),
            ];
        }

        $this->seedDynamoDbTable($items, self::TABLE_IDENTIFIER);

        $startKey = null;
        $retrievedItemsCount = 0;

        do {
            /** DynamoBreezeResult @result */
            $result = DynamoBreeze::withTableIdentifier(self::TABLE_IDENTIFIER)
                ->limit($pageSize)
                ->exclusiveStartKey($startKey)
                ->accessPattern('FetchUserPosts', ['user_id' => $userId])
                ->get();

            $items = $result->getItems();
            $retrievedItemsCount += $result->getCount();
            $startKey = $result->getLastEvaluatedKey();
        } while ($startKey !== null);

        $this->assertEquals($totalItems, $retrievedItemsCount);
    }

    public function testFindPostsByUserWithFilter(): void
    {
        $posts = [
            ['PK' => 'USER#1', 'SK' => 'POST#123', 'CategoryId' => 'A', 'Content' => 'Hello, World!', 'Timestamp' => time()],
            ['PK' => 'USER#1', 'SK' => 'POST#124', 'CategoryId' => 'B', 'Content' => 'My second post!', 'Timestamp' => time()],
        ];
        $this->seedDynamoDbTable($posts, self::TABLE_IDENTIFIER);

        $result = DynamoBreeze::withTableIdentifier(self::TABLE_IDENTIFIER)
            ->accessPattern('FindPostsByUserWithFilter', [
                'user_id' => 1,
                'category_id' => 'B',
            ])
            ->get();

        $this->assertEquals(1, $result->getCount());
        $this->assertEquals('B', $this->unmarshalItem($result->getItems()[0])['CategoryId']);
    }

    public function testFetchPostComments(): void
    {
        $comments = $this->generateCommentsData(1, 3);
        $this->seedDynamoDbTable($comments, self::TABLE_IDENTIFIER);

        $this->assertEquals(3, $this->fetchPostComments(1)->getCount());
    }

    public function testFetchPostLikes(): void
    {
        $likes = $this->generateLikesData(1, 2);
        $this->seedDynamoDbTable($likes, self::TABLE_IDENTIFIER);

        $this->assertEquals(2, $this->fetchPostLikes(1)->getCount());
    }

    public function testFetchPostsByDate(): void
    {
        $userId = 'User1';
        $now = Carbon::create(2023, 10, 15);
        $date = $now->format('Y-m-d');

        // Convert date to the start and end timestamps (inclusive) for that date
        $startDateTime = $now->startOfDay();
        $endDateTime = (clone $now)->endOfDay();

        // Seed Posts Data
        $post1Timestamp = (clone $startDateTime)->subDays(5)->timestamp; // Different date
        $post2Timestamp = (clone $startDateTime)->addMinutes(10)->timestamp;
        $post3Timestamp = $now->timestamp;

        $postsData = [
            $this->createPostData($userId, $post1Timestamp, 'Post 1 Content', 1),
            $this->createPostData($userId, $post2Timestamp, 'Post 2 Content', 2),
            $this->createPostData($userId, $post3Timestamp, 'Post 3 Content', 3),
        ];

        $this->seedDynamoDbTable($postsData, self::TABLE_IDENTIFIER);

        // Fetch Posts and Assert
        $fetchedPosts = $this->fetchPostsByDate(
            $userId,
            $startDateTime->getTimestamp(),
            $endDateTime->getTimestamp()
        );

        $this->assertEquals(2, $fetchedPosts->getCount());
        $this->assertPostsContainCorrectDates($fetchedPosts->getItems(), $date);
    }

    public function testFetchConversationMessages(): void
    {
        $user1 = 'User1';
        $user2 = 'User2';
        $conversationId = self::directConversationId($user1, $user2);

        // Track user conversation
        $participation = [
            $this->createParticipationData($user1, $conversationId, 'User2 Name', false),
            $this->createParticipationData($user2, $conversationId, 'User1 Name', false),
        ];

        $this->seedDynamoDbTable($participation, self::TABLE_IDENTIFIER);

        // Send messages and update last message content for use as snippet in conversation
        // listing
        $message1Timestamp = Carbon::now()->subMinutes(10)->timestamp;
        $message2Timestamp = Carbon::now()->subMinutes(9)->timestamp;

        $lastMessageTimestamp = Carbon::now()->subMinutes(5)->timestamp;
        $lastMessageContent = 'Message 3';
        $lastMessageSender = $user1;

        $messagesData = [
            $this->createMessageData($conversationId, $message1Timestamp, 'Message 1', $user1),
            $this->createMessageData($conversationId, $message2Timestamp, 'Message 2', $user2),
            $this->createMessageData($conversationId, $lastMessageTimestamp, $lastMessageContent, $lastMessageSender),
            // Update last message information on user conversation
            $this->createParticipationData(
                $user1,
                $conversationId,
                'User2 Name',
                false,
                $lastMessageTimestamp,
                $lastMessageContent
            ),
            $this->createParticipationData(
                $user2,
                $conversationId,
                'User1 Name',
                false,
                $lastMessageTimestamp,
                $lastMessageContent
            ),
        ];

        $this->seedDynamoDbTable($messagesData, self::TABLE_IDENTIFIER);

        $this->assertEquals(3, $this->fetchConversationMessages($conversationId)->getCount());
    }

    public function testFetchUserConversations(): void
    {
        $user1 = 'User1';
        $user2 = 'User2';
        $user3 = 'User3';
        $user4 = 'User4';

        $conversationId = self::directConversationId($user1, $user2);
        $conversation2Id = self::directConversationId($user1, $user3);
        $conversation3Id = self::directConversationId($user1, $user4);

        // Track user conversation
        $participation = [
            ['PK' => "USER#{$user1}", 'SK' => "CONVERSATION#{$conversationId}", 'ConversationName' => 'User2 Name', 'IsGroup' => false],
            ['PK' => "USER#{$user2}", 'SK' => "CONVERSATION#{$conversationId}", 'ConversationName' => 'User1 Name', 'IsGroup' => false],

            ['PK' => "USER#{$user1}", 'SK' => "CONVERSATION#{$conversation2Id}", 'ConversationName' => 'User3 Name', 'IsGroup' => false],
            ['PK' => "USER#{$user3}", 'SK' => "CONVERSATION#{$conversation2Id}", 'ConversationName' => 'User1 Name', 'IsGroup' => false],

            ['PK' => "USER#{$user1}", 'SK' => "CONVERSATION#{$conversation3Id}", 'ConversationName' => 'User4 Name', 'IsGroup' => false],
            ['PK' => "USER#{$user4}", 'SK' => "CONVERSATION#{$conversation3Id}", 'ConversationName' => 'User1 Name', 'IsGroup' => false],
        ];

        $this->seedDynamoDbTable($participation, self::TABLE_IDENTIFIER);

        $this->assertEquals(3, $this->fetchUserConversations($user1)->getCount());
    }

    public static function directConversationId(string $user1, string $user2): string
    {
        $id = strcmp($user1, $user2) < 0
            ? "{$user1}:{$user2}"
            : "{$user2}:{$user1}";

        return hash('sha256', $id);
    }
}
