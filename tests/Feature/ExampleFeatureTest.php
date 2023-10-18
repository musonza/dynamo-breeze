<?php

namespace Musonza\DynamoBreeze\Tests\Feature;

use Carbon\Carbon;
use Musonza\DynamoBreeze\Tests\FeatureTestCase;
use Musonza\DynamoBreeze\Tests\Traits\Helpers;

class ExampleFeatureTest extends FeatureTestCase
{
    use Helpers;

    private const TABLE_NAME = 'SocialMediaTable';

    private const TABLE_IDENTIFIER = 'social_media';

    public function setUp(): void
    {
        parent::setUp();
        $posts = [
            ['PK' => 'USER#1', 'SK' => 'POST#123', 'CategoryId' => 'A', 'Content' => 'Hello, World!', 'Timestamp' => time()],
            ['PK' => 'USER#1', 'SK' => 'POST#124', 'CategoryId' => 'B', 'Content' => 'My second post!', 'Timestamp' => time()],
        ];
        $this->seedDynamoDbTable($posts, self::TABLE_NAME);
    }

    public function testCreatesTablesFromConfiguration()
    {
        $this->assertTableExists(self::TABLE_NAME);
    }

    public function testFetchUserPosts(): void
    {
        $this->assertEquals(2, $this->fetchUserPosts(1)->getCount());
    }

    public function testFetchPostComments(): void
    {
        $comments = $this->generateCommentsData(1, 3);
        $this->seedDynamoDbTable($comments, self::TABLE_NAME);

        $this->assertEquals(3, $this->fetchPostComments(1)->getCount());
    }

    public function testFetchPostLikes(): void
    {
        $likes = $this->generateLikesData(1, 2);
        $this->seedDynamoDbTable($likes, self::TABLE_NAME);

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

        $this->seedDynamoDbTable($postsData, self::TABLE_NAME);

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

        $this->seedDynamoDbTable($participation, self::TABLE_NAME);

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

        $this->seedDynamoDbTable($messagesData, self::TABLE_NAME);

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

        $this->seedDynamoDbTable($participation, self::TABLE_NAME);

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
