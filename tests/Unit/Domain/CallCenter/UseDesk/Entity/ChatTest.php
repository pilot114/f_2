<?php

declare(strict_types=1);

namespace App\Tests\Unit\CallCenter\UseDesk\Entity;

use App\Domain\CallCenter\UseDesk\Entity\Chat;
use App\Domain\CallCenter\UseDesk\Entity\Message;

it('can add message to chat', function (Chat $chat, Message $message): void {
    $chat->addMessage($message);

    expect($chat->getMessages())->toHaveCount(2)
        ->and($chat->getMessages()[1])->toBe($message);
})->with('usedesk_chat', 'usedesk_message');

it('can set has answer status', function (Chat $chat): void {
    $chat->setHasAnswer(true);

    expect($chat->isHasAnswer())->toBeTrue();

    $chat->setHasAnswer(false);

    expect($chat->isHasAnswer())->toBeFalse();
})->with('usedesk_chat');

it('can set marked chat status', function (Chat $chat): void {
    expect($chat->isMarkedChat())->toBeFalse();

    $chat->setIsMarkedChat(true);

    expect($chat->isMarkedChat())->toBeTrue();
})->with('usedesk_chat');

it('can get first message', function (Chat $chat): void {
    $firstMessage = $chat->getFirstMessage();

    expect($firstMessage->id)->toBe(67890)
        ->and($firstMessage->text)->toBe('Тестовое сообщение от клиента');
})->with('usedesk_chat');

it('can convert to array', function (Chat $chat): void {
    $array = $chat->toArray();

    expect($array)->toHaveKeys([
        'id', 'status', 'messageId', 'clientName',
        'firstMessage', 'date', 'isMarkedChat', 'hasAnswer', 'messages',
    ])
        ->and($array['id'])->toBe(12345)
        ->and($array['clientName'])->toBe('Иван Иванов');
})->with('usedesk_chat');
