<?php

declare(strict_types=1);

namespace App\Tests\Unit\CallCenter\UseDesk\UseCase;

use App\Common\Helper\EnumerableWithTotal;
use App\Domain\CallCenter\UseDesk\Entity\MarkedChat;
use App\Domain\CallCenter\UseDesk\Repository\MarkedChatsQueryRepository;
use App\Domain\CallCenter\UseDesk\Service\UseDeskHttpClient;
use App\Domain\CallCenter\UseDesk\UseCase\GetChatsUseCase;
use DomainException;
use Mockery;

beforeEach(function (): void {
    $this->client = Mockery::mock(UseDeskHttpClient::class);
    $this->markedChatsRepository = Mockery::mock(MarkedChatsQueryRepository::class);

    $this->useCase = new GetChatsUseCase(
        $this->client,
        $this->markedChatsRepository,
    );
});

afterEach(function (): void {
    Mockery::close();
});

it('can get chats without filters', function (array $chatsResponse): void {
    $chatsResponse['meta']['last_page'] = 1;
    $this->client->shouldReceive('getChats')
        ->once()
        ->andReturn($chatsResponse);

    $this->markedChatsRepository->shouldReceive('findBy')
        ->once()
        ->withArgs(function ($args): bool {
            return is_array($args) && isset($args['chat_id']);
        })
        ->andReturn(EnumerableWithTotal::build([]));

    $result = $this->useCase->getChats(false, false);

    expect($result)->toHaveCount(1);
    $chat = $result->first();
    expect($chat->id)->toBe(12345)
        ->and($chat->isMarkedChat())->toBeFalse()
        ->and($chat->isHasAnswer())->toBeFalse();
})->with('usedesk_responses');

it('can get only marked chats', function (array $chatsResponse, MarkedChat $markedChat): void {
    $chatsResponse['meta']['last_page'] = 1;
    $this->client->shouldReceive('getChats')
        ->once()
        ->andReturn($chatsResponse);

    $this->markedChatsRepository->shouldReceive('findBy')
        ->once()
        ->withArgs(function ($args): bool {
            return is_array($args) && isset($args['chat_id']);
        })
        ->andReturn(EnumerableWithTotal::build([$markedChat]));

    $result = $this->useCase->getChats(true, false);

    expect($result)->toHaveCount(1);
    $chat = $result->first();
    expect($chat->isMarkedChat())->toBeTrue();
})->with('usedesk_responses_with_marked');

it('can get only chats without answer', function (array $chatsResponse): void {
    $chatsResponse['meta']['last_page'] = 1;
    $this->client->shouldReceive('getChats')
        ->once()
        ->andReturn($chatsResponse);

    $this->markedChatsRepository->shouldReceive('findBy')
        ->once()
        ->withArgs(function ($args): bool {
            return is_array($args) && isset($args['chat_id']);
        })
        ->andReturn(EnumerableWithTotal::build());

    $result = $this->useCase->getChats(false, true);

    expect($result)->toHaveCount(1);
    $chat = $result->first();
    expect($chat->isHasAnswer())->toBeFalse();
})->with('usedesk_responses');

it('returns empty collection when no chats found', function (): void {
    $this->client->shouldReceive('getChats')
        ->once()
        ->andReturn([
            'data' => [],
            'meta' => [
                'last_page'    => 1,
                'current_page' => 1,
            ],
        ]);

    $this->markedChatsRepository->shouldReceive('findBy')
        ->never();

    $result = $this->useCase->getChats(false, false);

    expect($result)->toHaveCount(0);
});

it('handles pagination correctly', function (): void {
    $firstPage = [
        'data' => [
            [
                'id'     => 1,
                'status' => 'opened',
                'client' => [
                    'id'   => 100,
                    'name' => 'Client 1',
                ],
                'messages' => [
                    [
                        'id'         => 1,
                        'from'       => 'client',
                        'text'       => 'Message 1',
                        'created_at' => '2024-01-15 10:00:00',
                    ],
                ],
            ],
        ],
        'meta' => [
            'last_page'    => 2,
            'current_page' => 1,
        ],
    ];

    $secondPage = [
        'data' => [
            [
                'id'     => 2,
                'status' => 'opened',
                'client' => [
                    'id'   => 200,
                    'name' => 'Client 2',
                ],
                'messages' => [
                    [
                        'id'         => 2,
                        'from'       => 'client',
                        'text'       => 'Message 2',
                        'created_at' => '2024-01-15 11:00:00',
                    ],
                ],
            ],
        ],
    ];

    $this->client->shouldReceive('getChats')
        ->once()
        ->withArgs(function ($args): bool {
            return !isset($args['page']);
        })
        ->andReturn($firstPage);

    $this->client->shouldReceive('getChats')
        ->once()
        ->withArgs(function (array $args): bool {
            return $args['page'] === 2;
        })
        ->andReturn($secondPage);

    $this->markedChatsRepository->shouldReceive('findBy')
        ->once()
        ->andReturn(EnumerableWithTotal::build([]));

    $result = $this->useCase->getChats(false, false);

    expect($result)->toHaveCount(2);
});

it('skips trigger messages', function (): void {
    $chatsResponse = [
        'data' => [
            [
                'id'     => 12345,
                'status' => 'opened',
                'client' => [
                    'id'   => 67890,
                    'name' => 'Test Client',
                ],
                'messages' => [
                    [
                        'id'         => 1,
                        'from'       => 'trigger',
                        'text'       => 'Trigger message',
                        'created_at' => '2024-01-15 10:00:00',
                    ],
                    [
                        'id'         => 2,
                        'from'       => 'client',
                        'text'       => 'Client message',
                        'created_at' => '2024-01-15 10:05:00',
                    ],
                ],
            ],
        ],
        'meta' => [
            'last_page'    => 1,
            'current_page' => 1,
        ],
    ];

    $this->client->shouldReceive('getChats')
        ->once()
        ->andReturn($chatsResponse);

    $this->markedChatsRepository->shouldReceive('findBy')
        ->once()
        ->andReturn(EnumerableWithTotal::build([]));

    $result = $this->useCase->getChats(false, false);

    expect($result)->toHaveCount(1);
    $chat = $result->first();
    $messages = $chat->getMessages();
    expect($messages)->toHaveCount(1);
    expect($messages[0]->text)->toBe('Client message');
});

it('throws exception when message has no user type', function (): void {
    $chatsResponse = [
        'data' => [
            [
                'id'     => 12345,
                'status' => 'opened',
                'client' => [
                    'id'   => 67890,
                    'name' => 'Test Client',
                ],
                'messages' => [
                    [
                        'id'         => 1,
                        'from'       => 'invalid_type',
                        'text'       => 'Message',
                        'created_at' => '2024-01-15 10:00:00',
                    ],
                ],
            ],
        ],
        'meta' => [
            'last_page'    => 1,
            'current_page' => 1,
        ],
    ];

    $this->client->shouldReceive('getChats')
        ->once()
        ->andReturn($chatsResponse);

    $this->markedChatsRepository->shouldReceive('findBy')
        ->never();

    expect(fn () => $this->useCase->getChats(false, false))
        ->toThrow(DomainException::class, 'у сообщения обязательно должен быть тип пользователя');
});
