<?php

declare(strict_types=1);

namespace App\Tests\Datasets;

use App\Domain\CallCenter\UseDesk\Entity\Chat;
use App\Domain\CallCenter\UseDesk\Entity\Client;
use App\Domain\CallCenter\UseDesk\Entity\MarkedChat;
use App\Domain\CallCenter\UseDesk\Entity\Message;
use App\Domain\CallCenter\UseDesk\Enum\StatusType;
use App\Domain\CallCenter\UseDesk\Enum\UserType;
use DateTimeImmutable;

function makeClient(): Client
{
    return new Client(
        id: 12345,
        name: 'Иван Иванов'
    );
}

function makeMessage(): Message
{
    return new Message(
        id: 67890,
        chatId: 12345,
        userType: UserType::CLIENT,
        text: 'Тестовое сообщение от клиента',
        date: new DateTimeImmutable()
    );
}

function makeUserMessage(): Message
{
    return new Message(
        id: 67891,
        chatId: 12345,
        userType: UserType::EMPLOYEE,
        text: 'Ответ от пользователя',
        date: new DateTimeImmutable()
    );
}

function makeChat(): Chat
{
    $chat = new Chat(
        id: 12345,
        status: StatusType::NEW,
        client: makeClient()
    );

    $chat->addMessage(makeMessage());
    $chat->setHasAnswer(false);

    return $chat;
}

function makeChatWithAnswer(): Chat
{
    $chat = new Chat(
        id: 12346,
        status: StatusType::REOPENED,
        client: makeClient()
    );

    $chat->addMessage(makeMessage());
    $chat->addMessage(makeUserMessage());
    $chat->setHasAnswer(true);

    return $chat;
}

function makeMarkedChat(): MarkedChat
{
    return new MarkedChat(
        id: 1,
        chatId: 12345,
        markDate: new DateTimeImmutable(),
        markUserId: 9999
    );
}

function makeChatRawData(): array
{
    return [
        'id'     => 12345,
        'status' => 'new',
        'client' => [
            'id'   => 12345,
            'name' => 'Иван Иванов',
        ],
        'messages' => [makeMessageRawData()],
    ];
}

function makeMessageRawData(): array
{
    return [
        'id'         => 67890,
        'from'       => 'client',
        'text'       => 'Тестовое сообщение от клиента',
        'created_at' => (new DateTimeImmutable())->format(DateTimeImmutable::ATOM),
    ];
}

function makeUseDeskChatsResponse(): array
{
    return [
        'data' => [
            makeChatRawData(),
        ],
        'meta' => [
            'last_page'    => 1,
            'current_page' => 1,
        ],
    ];
}

dataset('usedesk_client', [makeClient()]);
dataset('usedesk_message', [makeMessage()]);
dataset('usedesk_user_message', [makeUserMessage()]);
dataset('usedesk_chat', [makeChat()]);
dataset('usedesk_chat_with_answer', [makeChatWithAnswer()]);
dataset('usedesk_marked_chat', [makeMarkedChat()]);
dataset('usedesk_chat_raw_data', [makeChatRawData()]);
dataset('usedesk_message_raw_data', [makeMessageRawData()]);
dataset('usedesk_chats_response', [makeUseDeskChatsResponse()]);
dataset('usedesk_responses', [
    [makeUseDeskChatsResponse()],
]);
dataset('usedesk_responses_with_marked', [
    [makeUseDeskChatsResponse(), makeMarkedChat()],
]);
