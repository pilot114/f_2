<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\User\Cabinet\DTO;

use App\Domain\Portal\Cabinet\DTO\MessageToColleaguesResponse;
use App\Domain\Portal\Cabinet\Entity\MessageToColleagues;
use App\Domain\Portal\Cabinet\Entity\MessageToColleaguesNotification;
use App\Domain\Portal\Cabinet\Entity\User;
use DateTimeImmutable;

it('creates message to colleagues response with all fields', function (): void {
    $start = new DateTimeImmutable('2024-01-01');
    $end = new DateTimeImmutable('2024-12-31');
    $change = new DateTimeImmutable('2024-01-15');

    $response = new MessageToColleaguesResponse(
        id: 1,
        message: 'Hello colleagues!',
        startDate: $start,
        endDate: $end,
        changeDate: $change,
        userId: 42,
        notify: [[
            'id'   => 1,
            'name' => 'John',
        ]],
    );

    expect($response->id)->toBe(1)
        ->and($response->message)->toBe('Hello colleagues!')
        ->and($response->startDate)->toBe($start)
        ->and($response->endDate)->toBe($end)
        ->and($response->changeDate)->toBe($change)
        ->and($response->userId)->toBe(42)
        ->and($response->notify)->toHaveCount(1);
});

it('builds from message entity', function (): void {
    $user = new User(id: 5, name: 'Test User', email: 'test@example.com');
    $start = new DateTimeImmutable('2024-01-01');
    $end = new DateTimeImmutable('2024-12-31');
    $change = new DateTimeImmutable('2024-01-15');

    $message = new MessageToColleagues(
        id: 10,
        user: $user,
        message: 'Test message',
        startDate: $start,
        endDate: $end,
        changeDate: $change,
    );

    $response = MessageToColleaguesResponse::build($message);

    expect($response->id)->toBe(10)
        ->and($response->message)->toBe('Test message')
        ->and($response->userId)->toBe(5);
});

it('builds with notification users', function (): void {
    $user = new User(id: 1, name: 'Sender', email: 'sender@example.com');
    $notifUser1 = new User(id: 2, name: 'Recipient 1', email: 'recipient1@example.com');
    $notifUser2 = new User(id: 3, name: 'Recipient 2', email: 'recipient2@example.com');
    $date = new DateTimeImmutable();

    $message = new MessageToColleagues(
        id: 1,
        user: $user,
        message: 'Test',
        startDate: $date,
        endDate: $date,
        changeDate: $date,
    );

    $notification1 = new MessageToColleaguesNotification(
        id: 1,
        messageId: 1,
        user: $notifUser1,
    );

    $notification2 = new MessageToColleaguesNotification(
        id: 2,
        messageId: 1,
        user: $notifUser2,
    );

    $message->addNotification($notification1);
    $message->addNotification($notification2);

    $response = MessageToColleaguesResponse::build($message);

    expect($response->notify)->toHaveCount(2);
});

it('builds with empty notifications', function (): void {
    $user = new User(id: 1, name: 'Test', email: 'test@example.com');
    $date = new DateTimeImmutable();

    $message = new MessageToColleagues(
        id: 1,
        user: $user,
        message: 'Test',
        startDate: $date,
        endDate: $date,
        changeDate: $date,
    );

    $response = MessageToColleaguesResponse::build($message);

    expect($response->notify)->toBeEmpty();
});

it('handles cyrillic message', function (): void {
    $user = new User(id: 1, name: 'Тест', email: 'test@example.com');
    $date = new DateTimeImmutable();

    $message = new MessageToColleagues(
        id: 1,
        user: $user,
        message: 'Привет, коллеги!',
        startDate: $date,
        endDate: $date,
        changeDate: $date,
    );

    $response = MessageToColleaguesResponse::build($message);

    expect($response->message)->toBe('Привет, коллеги!');
});
