<?php

declare(strict_types=1);

namespace App\Tests\Unit\CallCenter\UseDesk\DTO;

use App\Common\Helper\EnumerableWithTotal;
use App\Domain\CallCenter\UseDesk\DTO\GetChatsResponse;
use App\Domain\CallCenter\UseDesk\Entity\Chat;
use App\Domain\CallCenter\UseDesk\Enum\StatusType;
use function App\Tests\Datasets\makeChat;
use function App\Tests\Datasets\makeClient;

it('can build response from chat collection', function (): void {
    $chat = makeChat();
    $collection = EnumerableWithTotal::build([$chat]);

    $response = GetChatsResponse::build($collection);

    expect($response->items)->toHaveCount(1)
        ->and($response->items[0])->toHaveKeys(['date', 'chatsCount', 'chats'])
        ->and($response->items[0]['chatsCount'])->toBe(1)
        ->and($response->items[0]['chats'])->toHaveCount(1)
        ->and($response->items[0]['chats'][0])->toHaveKeys([
            'id', 'status', 'messageId', 'clientName',
            'firstMessage', 'date', 'isMarkedChat', 'hasAnswer', 'messages',
        ])
        ->and($response->total)->toBe(1);
});

it('filters out chats without messages', function (): void {
    $chatWithMessages = makeChat();
    $chatWithoutMessages = new Chat(
        id: 99999,
        status: StatusType::NEW,
        client: makeClient()
    );

    $collection = EnumerableWithTotal::build([$chatWithMessages, $chatWithoutMessages]);

    $response = GetChatsResponse::build($collection);

    // Должен остаться только чат с сообщениями
    expect($response->items)->toHaveCount(1)
        ->and($response->items[0]['chatsCount'])->toBe(1)
        ->and($response->total)->toBe(2);
});
