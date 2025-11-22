<?php

declare(strict_types=1);

namespace App\Tests\Unit\User\Cabinet\UseCase;

use App\Common\Service\Integration\RpcClient;
use App\Domain\Portal\Cabinet\DTO\AddMessageToColleaguesRequest;
use App\Domain\Portal\Cabinet\Entity\MessageToColleagues;
use App\Domain\Portal\Cabinet\Entity\MessageToColleaguesNotification;
use App\Domain\Portal\Cabinet\Entity\User;
use App\Domain\Portal\Cabinet\Repository\MessageToColleaguesCommandRepository;
use App\Domain\Portal\Cabinet\Repository\MessageToColleaguesNotificationCommandRepository;
use App\Domain\Portal\Cabinet\Repository\MessageToColleaguesQueryRepository;
use App\Domain\Portal\Cabinet\Repository\UserQueryRepository;
use App\Domain\Portal\Cabinet\Service\ColleagueEmailer;
use App\Domain\Portal\Cabinet\UseCase\AddMessageToColleaguesUseCase;
use Database\Connection\TransactionInterface;
use DateTimeImmutable;
use Illuminate\Support\Collection;
use Mockery;
use Symfony\Component\HttpFoundation\Request;

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

beforeEach(function (): void {
    $this->messageQueryRepository = Mockery::mock(MessageToColleaguesQueryRepository::class);
    $this->messageCommandRepository = Mockery::mock(MessageToColleaguesCommandRepository::class);
    $this->notificationCommandRepository = Mockery::mock(MessageToColleaguesNotificationCommandRepository::class);
    $this->userQueryRepository = Mockery::mock(UserQueryRepository::class);
    $this->currentUser = createSecurityUser(9999, 'Иванов Иван', 'ivanov@test.com');
    $this->emailer = Mockery::mock(ColleagueEmailer::class);
    $this->transaction = Mockery::mock(TransactionInterface::class);
    $this->request = new Request();
    $this->rpcClient = Mockery::mock(RpcClient::class);

    $this->useCase = new AddMessageToColleaguesUseCase(
        $this->messageQueryRepository,
        $this->messageCommandRepository,
        $this->notificationCommandRepository,
        $this->userQueryRepository,
        $this->currentUser,
        $this->emailer,
        $this->transaction,
        $this->rpcClient
    );
});

afterEach(function (): void {
    Mockery::close();
});

it('creates new message when user has no existing message', function (): void {
    $request = new AddMessageToColleaguesRequest(
        startDate: new DateTimeImmutable('2025-01-01 00:00:00'),
        endDate: new DateTimeImmutable('2025-01-05 23:59:59'),
        message: 'Буду в отпуске',
        notifyUserIds: [5555]
    );

    $notifyUser = new User(5555, 'Петров Петр', 'petrov@test.com');
    $users = new Collection([$notifyUser]);

    $this->messageQueryRepository->shouldReceive('findMessageToColleagues')
        ->once()
        ->with($this->currentUser->id)
        ->andReturn(null);

    $this->transaction->shouldReceive('beginTransaction')->once();

    $this->messageCommandRepository->shouldReceive('create')
        ->once()
        ->withArgs(function (MessageToColleagues $message): bool {
            return $message->getMessage() === 'Буду в отпуске'
                && $message->user->id === 9999;
        })
        ->andReturn($message = new MessageToColleagues(
            1,
            new User(9999, 'Иванов Иван', 'ivanov@test.com'),
            'Буду в отпуске',
            $request->startDate,
            $request->endDate,
            new DateTimeImmutable()
        ));

    $this->notificationCommandRepository->shouldReceive('deleteNotifications')
        ->once()
        ->with(1);

    $this->userQueryRepository->shouldReceive('getUsersByIds')
        ->once()
        ->with([5555])
        ->andReturn($users);

    $this->notificationCommandRepository->shouldReceive('create')
        ->once()
        ->withArgs(function (MessageToColleaguesNotification $notification): bool {
            return $notification->messageId === 1
                && $notification->user->id === 5555;
        })
        ->andReturn(new MessageToColleaguesNotification(1, 1, $notifyUser));

    $this->transaction->shouldReceive('commit')->once();

    $this->rpcClient->shouldReceive('call')->once()
        ->withArgs(function (string $method, array $params) use ($message): bool {
            return $params['parameters']['hasActiveMessage'] === $message->isActive();
        });

    $this->emailer->shouldReceive('send')
        ->once()
        ->withArgs(function (array $emails, MessageToColleagues $message, string $senderName): bool {
            return $emails === ['petrov@test.com']
                && $message->getMessage() === 'Буду в отпуске'
                && $senderName === 'Иванов Иван';
        });

    $result = $this->useCase->add($request);

    expect($result)->toBeInstanceOf(MessageToColleagues::class);
    expect($result->getMessage())->toBe('Буду в отпуске');
});

it('updates existing message', function (MessageToColleagues $existingMessage): void {
    $request = new AddMessageToColleaguesRequest(
        startDate: new DateTimeImmutable('2025-02-01 00:00:00'),
        endDate: new DateTimeImmutable('2025-02-05 23:59:59'),
        message: 'Обновленное сообщение',
        notifyUserIds: []
    );

    $this->messageQueryRepository->shouldReceive('findMessageToColleagues')
        ->once()
        ->with($this->currentUser->id)
        ->andReturn($existingMessage);

    $this->transaction->shouldReceive('beginTransaction')->once();

    // Existing message is not expired, so no deletion should happen
    $this->messageCommandRepository->shouldNotReceive('delete');

    $this->messageCommandRepository->shouldReceive('update')
        ->once()
        ->with($existingMessage);

    $this->notificationCommandRepository->shouldReceive('deleteNotifications')
        ->once()
        ->with($existingMessage->getId());

    $this->transaction->shouldReceive('commit')->once();

    $this->rpcClient->shouldReceive('call')->once();

    $result = $this->useCase->add($request);

    expect($result)->toBe($existingMessage);
    expect($existingMessage->getMessage())->toBe('Обновленное сообщение');
})->with('messageToColleagues');

it('deletes and recreates expired message', function (MessageToColleagues $expiredMessage): void {
    $request = new AddMessageToColleaguesRequest(
        startDate: new DateTimeImmutable('2025-01-01 00:00:00'),
        endDate: new DateTimeImmutable('2025-01-05 23:59:59'),
        message: 'Новое сообщение',
        notifyUserIds: []
    );

    $this->messageQueryRepository->shouldReceive('findMessageToColleagues')
        ->once()
        ->with($this->currentUser->id)
        ->andReturn($expiredMessage);

    $this->transaction->shouldReceive('beginTransaction')->once();

    $this->notificationCommandRepository->shouldReceive('deleteNotifications')
        ->once()
        ->with($expiredMessage->getId());

    $this->messageCommandRepository->shouldReceive('delete')
        ->once()
        ->with($expiredMessage->getId());

    $this->messageCommandRepository->shouldReceive('create')
        ->once()
        ->withArgs(function (MessageToColleagues $message): bool {
            return $message->getMessage() === 'Новое сообщение';
        })
        ->andReturn($message = new MessageToColleagues(
            2,
            new User(9999, 'Иванов Иван', 'ivanov@test.com'),
            'Новое сообщение',
            $request->startDate,
            $request->endDate,
            new DateTimeImmutable()
        ));

    $this->notificationCommandRepository->shouldReceive('deleteNotifications')
        ->once()
        ->with(2);

    $this->transaction->shouldReceive('commit')->once();

    $this->rpcClient->shouldReceive('call')->once()
        ->withArgs(function (string $method, array $params) use ($message): bool {
            return $params['parameters']['hasActiveMessage'] === $message->isActive();
        });

    $result = $this->useCase->add($request);

    expect($result)->toBeInstanceOf(MessageToColleagues::class);
    expect($result->getMessage())->toBe('Новое сообщение');
})->with('expiredMessage');
