<?php

declare(strict_types=1);

namespace App\Tests\Unit\User\Cabinet\UseCase;

use App\Domain\Portal\Cabinet\DTO\GetMessageToColleaguesRequest;
use App\Domain\Portal\Cabinet\Entity\MessageToColleagues;
use App\Domain\Portal\Cabinet\Entity\MessageToColleaguesNotification;
use App\Domain\Portal\Cabinet\Entity\User;
use App\Domain\Portal\Cabinet\Repository\MessageToColleaguesNotificationQueryRepository;
use App\Domain\Portal\Cabinet\Repository\MessageToColleaguesQueryRepository;
use App\Domain\Portal\Cabinet\UseCase\GetMessageToColleaguesUseCase;
use DateTimeImmutable;
use Illuminate\Support\Collection;
use Mockery;

// Test datasets
dataset('messageToColleagues', [
    'active message' => [
        new MessageToColleagues(
            id: 1,
            user: new User(9999, 'Иванов Иван', 'ivanov@test.com'),
            message: 'Буду в отпуске с 1 по 5 января',
            startDate: new DateTimeImmutable('2024-12-01 00:00:00'),
            endDate: new DateTimeImmutable('2025-12-31 23:59:59'),
            changeDate: new DateTimeImmutable('2024-12-25 10:00:00')
        ),
    ],
]);

dataset('messageToColleaguesNotification', [
    'notification' => [
        new MessageToColleaguesNotification(
            id: 1,
            messageId: 1,
            user: new User(5555, 'Петров Петр', 'petrov@test.com')
        ),
    ],
]);

dataset('expiredMessage', [
    'expired message' => [
        new MessageToColleagues(
            id: 2,
            user: new User(9999, 'Иванов Иван', 'ivanov@test.com'),
            message: 'Это сообщение уже устарело',
            startDate: new DateTimeImmutable('2024-01-01 00:00:00'),
            endDate: new DateTimeImmutable('2024-01-05 23:59:59'),
            changeDate: new DateTimeImmutable('2023-12-25 10:00:00')
        ),
    ],
]);

dataset('futureMessage', [
    'future message' => [
        new MessageToColleagues(
            id: 3,
            user: new User(9999, 'Иванов Иван', 'ivanov@test.com'),
            message: 'Это сообщение запланировано на будущее',
            startDate: new DateTimeImmutable('2025-12-01 00:00:00'),
            endDate: new DateTimeImmutable('2025-12-05 23:59:59'),
            changeDate: new DateTimeImmutable('2025-11-25 10:00:00')
        ),
    ],
]);

beforeEach(function (): void {
    $this->messageQueryRepository = Mockery::mock(MessageToColleaguesQueryRepository::class);
    $this->notificationQueryRepository = Mockery::mock(MessageToColleaguesNotificationQueryRepository::class);
    $this->currentUser = createSecurityUser(9999, 'Иванов Иван', 'ivanov@test.com');

    $this->useCase = new GetMessageToColleaguesUseCase(
        $this->messageQueryRepository,
        $this->notificationQueryRepository,
        $this->currentUser
    );
});

afterEach(function (): void {
    Mockery::close();
});

it('returns message with notifications for author', function (MessageToColleagues $message, MessageToColleaguesNotification $notification): void {
    $request = new GetMessageToColleaguesRequest(userId: 9999);
    $notifications = new Collection([$notification]);

    $this->messageQueryRepository->shouldReceive('findMessageToColleagues')
        ->once()
        ->with(9999)
        ->andReturn($message);

    $this->notificationQueryRepository->shouldReceive('getNotificationsList')
        ->once()
        ->with($message->getId())
        ->andReturn($notifications);

    $result = $this->useCase->get($request);

    expect($result)->toBe($message);
    expect($result->getNotificationEmailsList())->toContain('petrov@test.com');
})->with('messageToColleagues', 'messageToColleaguesNotification');

it('returns message without notifications for non-author', function (): void {
    // Create a fresh message without any notifications
    $message = new MessageToColleagues(
        id: 1,
        user: new User(9999, 'Иванов Иван', 'ivanov@test.com'),
        message: 'Буду в отпуске с 1 по 5 января',
        startDate: new DateTimeImmutable('2024-12-01 00:00:00'),
        endDate: new DateTimeImmutable('2025-12-31 23:59:59'),
        changeDate: new DateTimeImmutable('2024-12-25 10:00:00')
    );

    $otherUser = createSecurityUser(5555, 'Петров Петр', 'petrov@test.com');
    $useCase = new GetMessageToColleaguesUseCase(
        $this->messageQueryRepository,
        $this->notificationQueryRepository,
        $otherUser
    );

    $request = new GetMessageToColleaguesRequest(userId: 9999);

    $this->messageQueryRepository->shouldReceive('findMessageToColleagues')
        ->once()
        ->with(9999)
        ->andReturn($message);

    $this->notificationQueryRepository->shouldNotReceive('getNotificationsList');

    $result = $useCase->get($request);

    expect($result)->toBe($message);
    expect($result->getNotificationEmailsList())->toBeEmpty();
});

it('returns null when message not found', function (): void {
    $request = new GetMessageToColleaguesRequest(userId: 9999);

    $this->messageQueryRepository->shouldReceive('findMessageToColleagues')
        ->once()
        ->with(9999)
        ->andReturn(null);

    $result = $this->useCase->get($request);

    expect($result)->toBeNull();
});

it('returns null for expired message', function (MessageToColleagues $expiredMessage): void {
    $request = new GetMessageToColleaguesRequest(userId: 9999);

    $this->messageQueryRepository->shouldReceive('findMessageToColleagues')
        ->once()
        ->with(9999)
        ->andReturn($expiredMessage);

    $result = $this->useCase->get($request);

    expect($result)->toBeNull();
})->with('expiredMessage');

it('returns null for future message when user is not author', function (MessageToColleagues $futureMessage): void {
    $otherUser = createSecurityUser(5555, 'Петров Петр', 'petrov@test.com');
    $useCase = new GetMessageToColleaguesUseCase(
        $this->messageQueryRepository,
        $this->notificationQueryRepository,
        $otherUser
    );

    $request = new GetMessageToColleaguesRequest(userId: 9999);

    $this->messageQueryRepository->shouldReceive('findMessageToColleagues')
        ->once()
        ->with(9999)
        ->andReturn($futureMessage);

    $result = $useCase->get($request);

    expect($result)->toBeNull();
})->with('futureMessage');

it('returns future message for author', function (MessageToColleagues $futureMessage, MessageToColleaguesNotification $notification): void {
    $request = new GetMessageToColleaguesRequest(userId: 9999);
    $notifications = new Collection([$notification]);

    $this->messageQueryRepository->shouldReceive('findMessageToColleagues')
        ->once()
        ->with(9999)
        ->andReturn($futureMessage);

    $this->notificationQueryRepository->shouldReceive('getNotificationsList')
        ->once()
        ->with($futureMessage->getId())
        ->andReturn($notifications);

    $result = $this->useCase->get($request);

    expect($result)->toBe($futureMessage);
    expect($result->getNotificationEmailsList())->toContain('petrov@test.com');
})->with('futureMessage', 'messageToColleaguesNotification');
