<?php

return [
    'FetchUserPosts' => [
        'key_condition_expression' => 'PK = :pk_val AND begins_with(SK, :sk_prefix_val)',
        'expression_attribute_values' => [
            ':pk_val' => ['S' => 'USER#<user_id>'],
            ':sk_prefix_val' => ['S' => 'POST#'],
        ],
    ],
    'FetchPostComments' => [
        'key_condition_expression' => 'PK = :pk_val AND begins_with(SK, :sk_prefix_val)',
        'expression_attribute_values' => [
            ':pk_val' => ['S' => 'POST#<post_id>'],
            ':sk_prefix_val' => ['S' => 'COMMENT#'],
        ],
    ],
    'FetchPostLikes' => [
        'key_condition_expression' => 'PK = :pk_val AND begins_with(SK, :sk_prefix_val)',
        'expression_attribute_values' => [
            ':pk_val' => ['S' => 'POST#<post_id>'],
            ':sk_prefix_val' => ['S' => 'LIKE#'],
        ],
    ],
    /**
     * For messaging, we have users one-to-one messages as well as group messages
     *
     * Primary Key:
     * PK: CONVERSATION#<ConversationId>
     * SK: #MESSAGE#<Timestamp>
     *
     * Attributes:
     * MessageContent (String): The content of the message.
     * SenderUserId (String): The ID of the user who sent the message.
     *
     * For keeping track of all conversations (groups and one-to-one) a user is a part of:
     *
     * Primary Key:
     * PK: USER#<UserId>
     * SK: CONVERSATION#<ConversationId>
     *
     * Attributes:
     * ConversationName (String): The name of the conversation or group.
     * LastMessageTimestamp (Number): Timestamp of the last message in the conversation.
     * LastMessageContent (String): A snippet or the full content of the last message.
     * UnreadMessages (Number): Count of unread messages in the conversation.
     * IsGroup (Boolean): Flag indicating if the conversation is a group conversation.
     */
    'FetchConversationMessages' => [
        'key_condition_expression' => 'PK = :pk_val AND begins_with(SK, :sk_prefix_val)',
        'expression_attribute_values' => [
            ':pk_val' => ['S' => 'CONVERSATION#<conversation_id>'],
            ':sk_prefix_val' => ['S' => 'MESSAGE#'],
        ],
    ],
    'FetchUserConversations' => [
        'key_condition_expression' => 'PK = :pk_val AND begins_with(SK, :sk_prefix_val)',
        'expression_attribute_values' => [
            ':pk_val' => ['S' => 'USER#<user_id>'],
            ':sk_prefix_val' => ['S' => 'CONVERSATION#'],
        ],
    ],
    'FetchPostsByDate' => [
        'gsi_name' => 'GSI1',
        'key_condition_expression' => '#partitionKey = :partitionValue AND #sortKey BETWEEN :startSortKey AND :endSortKey',
        'expression_attribute_names' => [
            '#partitionKey' => 'GSI1PK',
            '#sortKey' => 'GSI1SK',
            '#timestamp' => 'Timestamp',  // using an alias for 'Timestamp' since it's a reserved word
        ],
        'expression_attribute_values' => [
            ':partitionValue' => [
                'S' => 'USER#<user_id>',
            ],
            ':startSortKey' => [
                'S' => 'POST#<start>',
            ],
            ':endSortKey' => [
                'S' => 'POST#<end>',
            ],
        ],
        'projection_expression' => 'PK, SK, Content, #timestamp',
        'scan_index_forward' => false,
        'limit' => 10,
    ],
    'FindPostsByUserWithFilter' => [
        'key_condition_expression' => 'PK = :pk_val AND begins_with(SK, :sk_prefix_val)',
        'filter_expression' => 'CategoryId = :CategoryId',
        'expression_attribute_values' => [
            ':pk_val' => ['S' => 'USER#<user_id>'],
            ':sk_prefix_val' => ['S' => 'POST#'],
            ':CategoryId' => ['S' => '<category_id>'],
        ],
    ],
];
