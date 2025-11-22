<?php

declare(strict_types=1);

namespace App\Tests\Unit\User\Cabinet\Repository;

use App\Domain\Portal\Cabinet\Entity\User;
use App\Domain\Portal\Cabinet\Repository\UserQueryRepository;
use Closure;
use Database\Connection\ParamType;
use Database\Connection\ReadDatabaseInterface;
use Generator;
use Mockery;

it('finds active users with email by search', function (): void {
    $connection = Mockery::mock(ReadDatabaseInterface::class);
    $repository = new UserQueryRepository($connection, getDataMapper());
    $searchTerm = 'Иванов';

    $data = [
        [
            'id'             => '9999',
            'name'           => 'Иванов Иван',
            'email'          => 'ivanov@test.com',
            'responses_id'   => '1',
            'responses_name' => 'Отдел разработки',
        ],
    ];

    $generator = function (array $data): Closure {
        return function () use ($data): Generator {
            foreach ($data as $item) {
                yield $item;
            }
        };
    };

    $connection->shouldReceive('query')
        ->once()
        ->withArgs(function ($sql, array $params) use ($searchTerm): bool {
            return str_contains($sql, 'e.active = \'Y\'')
                && str_contains($sql, 'lower(e.name) LIKE')
                && $params['search'] === $searchTerm;
        })
        ->andReturnUsing($generator($data));

    $result = $repository->findActiveUsersWithEmail($searchTerm);

    expect($result->first())->toBeInstanceOf(User::class);
    expect($result->first()->name)->toBe('Иванов Иван');
    expect($result->first()->email)->toBe('ivanov@test.com');
});

it('finds users by ids', function (): void {
    $connection = Mockery::mock(ReadDatabaseInterface::class);
    $repository = new UserQueryRepository($connection, getDataMapper());
    $userIds = [9999, 5555];

    $data = [
        [
            'id'             => '9999',
            'name'           => 'Иванов Иван',
            'email'          => 'ivanov@test.com',
            'responses_id'   => '1',
            'responses_name' => 'Отдел разработки',
        ],
        [
            'id'             => '5555',
            'name'           => 'Петров Петр',
            'email'          => 'petrov@test.com',
            'responses_id'   => '2',
            'responses_name' => 'Отдел тестирования',
        ],
    ];

    $generator = function (array $data): Closure {
        return function () use ($data): Generator {
            foreach ($data as $item) {
                yield $item;
            }
        };
    };

    $connection->shouldReceive('query')
        ->once()
        ->withArgs(function ($sql, array $params, array $types) use ($userIds): bool {
            return str_contains($sql, 'e.id in (:userIds)')
                && $params['userIds'] === $userIds
                && $types['userIds'] === ParamType::ARRAY_INTEGER;
        })
        ->andReturnUsing($generator($data));

    $result = $repository->getUsersByIds($userIds);

    expect($result->count())->toBe(2);
    expect($result->first()->name)->toBe('Иванов Иван');
});
