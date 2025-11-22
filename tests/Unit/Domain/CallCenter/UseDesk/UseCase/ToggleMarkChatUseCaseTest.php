<?php

declare(strict_types=1);

namespace App\Tests\Unit\CallCenter\UseDesk\UseCase;

use App\Domain\CallCenter\UseDesk\Entity\MarkedChat;
use App\Domain\CallCenter\UseDesk\Repository\MarkChatCommandRepository;
use App\Domain\CallCenter\UseDesk\Repository\MarkedChatsQueryRepository;
use App\Domain\CallCenter\UseDesk\UseCase\ToggleMarkChatUseCase;
use DomainException;
use Mockery;

beforeEach(function (): void {
    $this->commandRepository = Mockery::mock(MarkChatCommandRepository::class);
    $this->queryRepository = Mockery::mock(MarkedChatsQueryRepository::class);
    $this->currentUser = createSecurityUser(
        id: 9999,
        email: 'test@test.com',
    );

    $this->useCase = new ToggleMarkChatUseCase(
        $this->commandRepository,
        $this->queryRepository,
        $this->currentUser
    );
});

afterEach(function (): void {
    Mockery::close();
});

it('can mark chat', function (MarkedChat $markedChat): void {
    $chatId = 12345;

    $this->queryRepository->shouldReceive('findOneBy')
        ->once()
        ->with([
            'chat_id' => $chatId,
        ])
        ->andReturn(null);

    $this->commandRepository->shouldReceive('markChat')
        ->once()
        ->andReturn($markedChat);

    $result = $this->useCase->markChat($chatId);

    expect($result)->toBe($markedChat);
})->with('usedesk_marked_chat');

it('throws exception when marking already marked chat', function (MarkedChat $markedChat): void {
    $chatId = 12345;

    $this->queryRepository->shouldReceive('findOneBy')
        ->once()
        ->with([
            'chat_id' => $chatId,
        ])
        ->andReturn($markedChat);

    $this->commandRepository->shouldReceive('markChat')
        ->never();

    expect(fn () => $this->useCase->markChat($chatId))
        ->toThrow(DomainException::class, "чат с id = {$markedChat->chatId} уже отмечен");
})->with('usedesk_marked_chat');

it('can unmark chat', function (MarkedChat $markedChat): void {
    $chatId = 12345;

    $this->queryRepository->shouldReceive('findOneBy')
        ->once()
        ->with([
            'chat_id' => $chatId,
        ])
        ->andReturn($markedChat);

    $this->commandRepository->shouldReceive('unmarkChat')
        ->once()
        ->with($markedChat)
        ->andReturn(true);

    $result = $this->useCase->unmarkChat($chatId);

    expect($result)->toBeTrue();
})->with('usedesk_marked_chat');

it('throws exception when unmarking not marked chat', function (): void {
    $chatId = 12345;

    $this->queryRepository->shouldReceive('findOneBy')
        ->once()
        ->with([
            'chat_id' => $chatId,
        ])
        ->andReturn(null);

    $this->commandRepository->shouldReceive('unmarkChat')
        ->never();

    expect(fn () => $this->useCase->unmarkChat($chatId))
        ->toThrow(DomainException::class, "чат с id = $chatId не отмечен");
});
