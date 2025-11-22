<?php

declare(strict_types=1);

namespace App\Tests\Unit\Events\Rewards\Repository;

use App\Domain\Events\Rewards\Entity\Reward;
use App\Domain\Events\Rewards\Repository\RewardQueryRepository;
use Closure;
use Database\Connection\ParamType;
use Database\Connection\ReadDatabaseInterface;
use Database\EntityNotFoundDatabaseException;
use Generator;
use Illuminate\Support\Enumerable;
use Mockery;

it('get by reward ids', function (array $rawDataFromDb, array $rewardIds, bool $missing): void {
    $connection = Mockery::mock(ReadDatabaseInterface::class);
    $repository = new RewardQueryRepository($connection, getDataMapper());

    $generator = function (array $data): Closure {
        return function () use ($data): Generator {
            foreach ($data as $item) {
                yield $item;
            }
        };
    };

    $connection->shouldReceive('query')
        ->once()
        ->withArgs(function ($sql, array $params, array $type) use ($rewardIds): bool {
            return $params['ids'] === $rewardIds
                && $type['ids'] === ParamType::ARRAY_INTEGER;
        })
        ->andReturnUsing($generator($rawDataFromDb));

    if ($missing) {
        expect(fn (): Enumerable => $repository->getByIds($rewardIds))->toThrow(EntityNotFoundDatabaseException::class);
        return;
    }

    $rewards = $repository->getByIds($rewardIds);
    expect($rewards->count())->toBe(2);

})->with('rewards by ids');

it('get one by id', function (array $rawDataFromDb, int $rewardId, bool $missing): void {
    $connection = Mockery::mock(ReadDatabaseInterface::class);
    $repository = new RewardQueryRepository($connection, getDataMapper());

    $generator = function (array $data): Closure {
        return function () use ($data): Generator {
            foreach ($data as $item) {
                yield $item;
            }
        };
    };

    $connection->shouldReceive('query')
        ->once()
        ->withArgs(function ($sql, array $params) use ($rewardId): bool {
            return $params['id'] === $rewardId;
        })
        ->andReturnUsing($generator($rawDataFromDb));

    if ($missing) {
        expect(fn (): Reward => $repository->getOne($rewardId))->toThrow(EntityNotFoundDatabaseException::class);
        return;
    }

    $reward = $repository->getOne($rewardId);
    expect($reward)->toBeInstanceOf(Reward::class);
    expect($reward->id)->toBe((int) $rawDataFromDb[0]['id']);

})->with('reward by id');
