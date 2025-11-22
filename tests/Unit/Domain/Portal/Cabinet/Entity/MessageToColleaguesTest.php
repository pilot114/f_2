<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\User\Cabinet\Entity;

use App\Domain\Portal\Cabinet\Entity\MessageToColleagues;
use App\Domain\Portal\Cabinet\Entity\MessageToColleaguesNotification;
use App\Domain\Portal\Cabinet\Entity\User;
use DateTimeImmutable;

it('creates message with all fields', function (): void {
    $user = new User(id: 1, name: 'Test User', email: 'test@example.com');
    $start = new DateTimeImmutable('2024-01-01');
    $end = new DateTimeImmutable('2024-12-31');
    $change = new DateTimeImmutable('2024-01-15');

    $message = new MessageToColleagues(
        id: 1,
        user: $user,
        message: 'Hello colleagues!',
        startDate: $start,
        endDate: $end,
        changeDate: $change,
    );

    expect($message->getId())->toBe(1)
        ->and($message->user)->toBe($user)
        ->and($message->getMessage())->toBe('Hello colleagues!')
        ->and($message->getStartDate())->toBe($start)
        ->and($message->getEndDate())->toBe($end)
        ->and($message->getChangeDate())->toBe($change);
});

it('checks if message is actual', function (): void {
    $user = new User(id: 1, name: 'Test', email: 'test@example.com');
    $start = new DateTimeImmutable('2024-01-01');
    $end = new DateTimeImmutable('+1 day');
    $change = new DateTimeImmutable();

    $message = new MessageToColleagues(
        id: 1,
        user: $user,
        message: 'Test',
        startDate: $start,
        endDate: $end,
        changeDate: $change,
    );

    expect($message->isActual())->toBeTrue();
});

it('checks if message is not actual', function (): void {
    $user = new User(id: 1, name: 'Test', email: 'test@example.com');
    $start = new DateTimeImmutable('2023-01-01');
    $end = new DateTimeImmutable('2023-12-31');
    $change = new DateTimeImmutable();

    $message = new MessageToColleagues(
        id: 1,
        user: $user,
        message: 'Test',
        startDate: $start,
        endDate: $end,
        changeDate: $change,
    );

    expect($message->isActual())->toBeFalse();
});

it('checks if message is in future', function (): void {
    $user = new User(id: 1, name: 'Test', email: 'test@example.com');
    $start = new DateTimeImmutable('+1 day');
    $end = new DateTimeImmutable('+2 days');
    $change = new DateTimeImmutable();

    $message = new MessageToColleagues(
        id: 1,
        user: $user,
        message: 'Test',
        startDate: $start,
        endDate: $end,
        changeDate: $change,
    );

    expect($message->isInFuture())->toBeTrue();
});

it('checks if message is active', function (): void {
    $user = new User(id: 1, name: 'Test', email: 'test@example.com');
    $start = new DateTimeImmutable('-1 day');
    $end = new DateTimeImmutable('+1 day');
    $change = new DateTimeImmutable();

    $message = new MessageToColleagues(
        id: 1,
        user: $user,
        message: 'Test',
        startDate: $start,
        endDate: $end,
        changeDate: $change,
    );

    expect($message->isActive())->toBeTrue();
});

it('edits message', function (): void {
    $user = new User(id: 1, name: 'Test', email: 'test@example.com');
    $oldStart = new DateTimeImmutable('2024-01-01');
    $oldEnd = new DateTimeImmutable('2024-01-31');
    $change = new DateTimeImmutable('2024-01-01');

    $message = new MessageToColleagues(
        id: 1,
        user: $user,
        message: 'Old message',
        startDate: $oldStart,
        endDate: $oldEnd,
        changeDate: $change,
    );

    $newStart = new DateTimeImmutable('2024-02-01');
    $newEnd = new DateTimeImmutable('2024-02-28');

    $message->edit('New message', $newStart, $newEnd);

    expect($message->getMessage())->toBe('New message')
        ->and($message->getStartDate())->toBe($newStart)
        ->and($message->getEndDate())->toBe($newEnd);
});

it('sets message text', function (): void {
    $user = new User(id: 1, name: 'Test', email: 'test@example.com');
    $date = new DateTimeImmutable();

    $message = new MessageToColleagues(
        id: 1,
        user: $user,
        message: 'Original',
        startDate: $date,
        endDate: $date,
        changeDate: $date,
    );

    $message->setMessage('Updated');

    expect($message->getMessage())->toBe('Updated');
});

it('adds notification', function (): void {
    $user = new User(id: 1, name: 'Test', email: 'test@example.com');
    $notifUser = new User(id: 2, name: 'Recipient', email: 'recipient@example.com');
    $date = new DateTimeImmutable();

    $message = new MessageToColleagues(
        id: 1,
        user: $user,
        message: 'Test',
        startDate: $date,
        endDate: $date,
        changeDate: $date,
    );

    $notification = new MessageToColleaguesNotification(
        id: 1,
        messageId: 1,
        user: $notifUser,
    );

    $message->addNotification($notification);

    $emails = $message->getNotificationEmailsList();

    expect($emails)->toContain('recipient@example.com');
});

it('returns notification emails list', function (): void {
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

    $notif1 = new MessageToColleaguesNotification(
        id: 1,
        messageId: 1,
        user: new User(id: 2, name: 'User 1', email: 'user1@example.com'),
    );

    $notif2 = new MessageToColleaguesNotification(
        id: 2,
        messageId: 1,
        user: new User(id: 3, name: 'User 2', email: 'user2@example.com'),
    );

    $message->addNotification($notif1);
    $message->addNotification($notif2);

    $emails = $message->getNotificationEmailsList();

    expect($emails)->toHaveCount(2)
        ->and($emails)->toContain('user1@example.com')
        ->and($emails)->toContain('user2@example.com');
});

it('returns notification users', function (): void {
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

    $recipientUser = new User(id: 2, name: 'Recipient', email: 'recipient@example.com');

    $notification = new MessageToColleaguesNotification(
        id: 1,
        messageId: 1,
        user: $recipientUser,
    );

    $message->addNotification($notification);

    $users = $message->getNotificationUsers();

    expect($users)->toHaveCount(1)
        ->and($users[0])->toBe($recipientUser);
});
