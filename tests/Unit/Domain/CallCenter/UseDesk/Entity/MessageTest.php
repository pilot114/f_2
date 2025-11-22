<?php

declare(strict_types=1);

namespace App\Tests\Unit\CallCenter\UseDesk\Entity;

use App\Domain\CallCenter\UseDesk\Entity\Message;

it('can check if message is sent by user', function (Message $message): void {
    expect($message->isSendByUser())->toBeFalse();
})->with('usedesk_message');

it('can check if message is sent by user when user type is user', function (Message $userMessage): void {
    expect($userMessage->isSendByUser())->toBeTrue();
})->with('usedesk_user_message');

it('can convert to array', function (Message $message): void {
    $array = $message->toArray();

    expect($array)->toHaveKeys(['id', 'userType', 'message', 'date'])
        ->and($array['id'])->toBe(67890)
        ->and($array['userType'])->toBe('client')
        ->and($array['message'])->toBe('Тестовое сообщение от клиента');
})->with('usedesk_message');
