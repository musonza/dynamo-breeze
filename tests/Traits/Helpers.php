<?php

namespace Musonza\DynamoBreeze\Tests\Traits;

use Aws\DynamoDb\Exception\DynamoDbException;
use Aws\DynamoDb\Marshaler;
use Carbon\Carbon;
use Musonza\DynamoBreeze\DynamoBreezeResult;
use Musonza\DynamoBreeze\Facades\DynamoBreeze;

trait Helpers
{
    private function assertTableExists(string $tableName): void
    {
        try {
            $result = $this->client->describeTable(['TableName' => $tableName]);
            $this->assertEquals($tableName, $result['Table']['TableName']);
        } catch (DynamoDbException $e) {
            $this->fail("Table [$tableName] was not created. ".$e->getMessage());
        }
    }

    private function fetchUserPosts(int $userId): DynamoBreezeResult
    {
        return DynamoBreeze::table(self::TABLE_IDENTIFIER)
            ->accessPattern('FetchUserPosts', ['user_id' => $userId])
            ->get();
    }

    private function fetchPostComments(int $postId): DynamoBreezeResult
    {
        return DynamoBreeze::table(self::TABLE_IDENTIFIER)
            ->accessPattern('FetchPostComments', ['post_id' => $postId])
            ->get();
    }

    private function fetchPostLikes(int $postId): DynamoBreezeResult
    {
        return DynamoBreeze::table(self::TABLE_IDENTIFIER)
            ->accessPattern('FetchPostLikes', ['post_id' => $postId])
            ->get();
    }

    private function fetchConversationMessages(string $conversationId): DynamoBreezeResult
    {
        return DynamoBreeze::table(self::TABLE_IDENTIFIER)
            ->accessPattern('FetchConversationMessages', ['conversation_id' => $conversationId])
            ->get();
    }

    private function fetchUserConversations(string $userId)
    {
        return DynamoBreeze::table(self::TABLE_IDENTIFIER)
            ->accessPattern('FetchUserConversations', ['user_id' => $userId])
            ->get();
    }

    private function fetchPostsByDate(string $userId, $start, $end)
    {
        return DynamoBreeze::table(self::TABLE_IDENTIFIER)
            ->accessPattern('FetchPostsByDate', [
                'user_id' => $userId,
                'start' => $start,
                'end' => $end,
            ])
            ->get();
    }

    private function generateCommentsData(int $postId, int $numComments): array
    {
        $comments = [];
        for ($i = 1; $i <= $numComments; $i++) {
            $comments[] = [
                'PK' => "POST#$postId",
                'SK' => "COMMENT#$i",
                'Content' => "Comment $i",
                'Timestamp' => time(),
            ];
        }

        return $comments;
    }

    private function generateLikesData(int $postId, int $numLikes)
    {
        $likes = [];
        for ($i = 1; $i <= $numLikes; $i++) {
            $likes[] = [
                'PK' => "POST#$postId",
                'SK' => "LIKE#$i",
                'Timestamp' => time(),
            ];
        }

        return $likes;
    }

    private function createPostData($userId, $timestamp, $content, $postId): array
    {
        return [
            'PK' => "USER#{$userId}",
            'SK' => "POST#{$postId}",
            'GSI1PK' => "USER#{$userId}",
            'GSI1SK' => "POST#{$timestamp}",
            'Content' => $content,
            'Timestamp' => $timestamp,
        ];
    }

    private function assertPostsContainCorrectDates($posts, $expectedDate): void
    {
        $marshaler = new Marshaler();
        foreach ($posts as $post) {
            $post = $marshaler->unmarshalItem($post);
            $postDate = Carbon::createFromTimestamp($post['Timestamp']);
            $this->assertEquals($expectedDate, $postDate->format('Y-m-d'));
        }
    }

    private function createMessageData($conversationId, $timestamp, $content, $senderUserId): array
    {
        return [
            'PK' => "CONVERSATION#{$conversationId}",
            'SK' => "MESSAGE#{$timestamp}",
            'MessageContent' => $content,
            'SenderUserId' => $senderUserId,
            'Timestamp' => $timestamp,
        ];
    }

    private function createParticipationData(
        $userId,
        $conversationId,
        $conversationName,
        $isGroup,
        $lastMessageTimestamp = null,
        $lastMessageContent = null
    ) {
        $data = [
            'PK' => "USER#{$userId}",
            'SK' => "CONVERSATION#{$conversationId}",
            'ConversationName' => $conversationName,
            'IsGroup' => $isGroup,
        ];

        if ($lastMessageTimestamp !== null && $lastMessageContent !== null) {
            $data['LastMessageTimestamp'] = $lastMessageTimestamp;
            $data['LastMessageContent'] = $lastMessageContent;
        }

        return $data;
    }

    private function assertConversationMessageCount($expectedCount, $conversationId)
    {
        $actualCount = $this->fetchConversationMessages($conversationId)->getCount();
        $this->assertEquals($expectedCount, $actualCount);
    }
}
