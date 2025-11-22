<?php

declare(strict_types=1);

namespace App\Tests\Unit\User\Cabinet\UseCase;

use App\Common\Service\Integration\RpcClient;
use App\Domain\Portal\Cabinet\Entity\MessageToColleagues;
use App\Domain\Portal\Cabinet\Entity\User;
use App\Domain\Portal\Cabinet\Repository\MessageToColleaguesCommandRepository;
use App\Domain\Portal\Cabinet\Repository\MessageToColleaguesNotificationCommandRepository;
use App\Domain\Portal\Cabinet\Repository\MessageToColleaguesQueryRepository;
use App\Domain\Portal\Cabinet\UseCase\DeleteMessageToColleaguesUseCase;
use DateTimeImmutable;
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

beforeEach(function (): void {
    $this->messageQueryRepository = Mockery::mock(MessageToColleaguesQueryRepository::class);
    $this->messageCommandRepository = Mockery::mock(MessageToColleaguesCommandRepository::class);
    $this->notificationCommandRepository = Mockery::mock(MessageToColleaguesNotificationCommandRepository::class);
    $this->currentUser = createSecurityUser(9999, 'Иванов Иван', 'ivanov@test.com');
    $this->request = new Request();
    $this->rpcClient = Mockery::mock(RpcClient::class);

    $this->useCase = new DeleteMessageToColleaguesUseCase(
        $this->messageQueryRepository,
        $this->messageCommandRepository,
        $this->notificationCommandRepository,
        $this->currentUser,
        $this->rpcClient
    );
});

afterEach(function (): void {
    Mockery::close();
});

it('successfully deletes message and returns true', function (MessageToColleagues $message): void {
    $this->messageQueryRepository->shouldReceive('findMessageToColleagues')
        ->once()
        ->with($this->currentUser->id)
        ->andReturn($message);

    $this->notificationCommandRepository->shouldReceive('deleteNotifications')
        ->once()
        ->with($message->getId());

    $this->messageCommandRepository->shouldReceive('delete')
        ->once()
        ->with($message->getId());

    $this->rpcClient->shouldReceive('call')->once()
        ->withArgs(function (string $method, array $params): bool {
            return $params['parameters']['hasActiveMessage'] === false;
        });

    $result = $this->useCase->delete();

    expect($result)->toBeTrue();
})->with('messageToColleagues');

it('returns false when message not found', function (): void {
    $this->messageQueryRepository->shouldReceive('findMessageToColleagues')
        ->once()
        ->with($this->currentUser->id)
        ->andReturn(null);

    $result = $this->useCase->delete();

    expect($result)->toBeFalse();
});
