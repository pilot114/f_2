<?php

declare(strict_types=1);

namespace App\Tests\Unit\Events\Rewards\Repository;

use App\Domain\Events\Rewards\Entity\Program;
use App\Domain\Events\Rewards\Entity\Reward;
use App\Domain\Events\Rewards\Repository\ProgramQueryRepository;
use Closure;
use Database\Connection\ParamType;
use Database\Connection\ReadDatabaseInterface;
use Database\EntityNotFoundDatabaseException;
use Generator;
use Illuminate\Support\Enumerable;
use Mockery;

it('get program by ids', function (array $programIds, array $rawDataFromDb, bool $missingProgram): void {

    $connection = Mockery::mock(ReadDatabaseInterface::class);
    $repository = new ProgramQueryRepository($connection, getDataMapper());

    $generator = function (array $data): Closure {
        return function () use ($data): Generator {
            foreach ($data as $item) {
                yield $item;
            }
        };
    };

    $connection->shouldReceive('query')
        ->once()
        ->withArgs(function ($sql, array $params, array $type) use ($programIds): bool {
            return $params['ids'] === $programIds
                && $type['ids'] === ParamType::ARRAY_INTEGER;
        })
        ->andReturnUsing($generator($rawDataFromDb));

    if ($missingProgram) {
        expect(fn (): Enumerable => $repository->getByIds($programIds))->toThrow(EntityNotFoundDatabaseException::class);
        return;
    }

    $programs = $repository->getByIds($programIds);
    expect($programs->count())->toBe(2);

})->with('programs list');

it('get program by rewards', function (array $rewards, array $programs, bool $empty): void {

    $connection = Mockery::mock(ReadDatabaseInterface::class);
    $repository = new ProgramQueryRepository($connection, getDataMapper());

    $rewardCollection = collect();
    foreach ($rewards as $reward) {
        $rewardCollection->push(new Reward(...$reward));
    }

    $generator = function (array $data): Closure {
        return function () use ($data): Generator {
            foreach ($data as $item) {
                yield $item;
            }
        };
    };

    $connection->shouldReceive('query')
        ->once()
        ->withArgs(function ($sql, array $params, array $type) use ($rewardCollection): bool {
            return $params['rewardIds'] === $rewardCollection->map(fn (Reward $reward): int => $reward->id)->values()->all()
                && $type['rewardIds'] === ParamType::ARRAY_INTEGER;
        })
        ->andReturnUsing($generator($programs));

    if ($empty) {
        expect(fn (): Enumerable => $repository->getByRewards($rewardCollection))->toThrow(EntityNotFoundDatabaseException::class);
        return;
    }

    $programs = $repository->getByRewards($rewardCollection);
    expect($programs->first())->toBeInstanceOf(Program::class);

})->with('rewards with programs list');

it('get programs for verification filter', function (): void {

    $connection = Mockery::mock(ReadDatabaseInterface::class);
    $repository = new ProgramQueryRepository($connection, getDataMapper());

    $generator = function (array $data): Closure {
        return function () use ($data): Generator {
            foreach ($data as $item) {
                yield $item;
            }
        };
    };

    $connection->shouldReceive('query')
        ->once()
        ->andReturnUsing($generator([
            [
                'id'   => '1',
                'name' => 'Программа 1',
            ],
        ]));

    $programs = $repository->getProgramsForVerificationFilter();
    foreach ($programs as $program) {
        expect($program)->toBeInstanceOf(Program::class);
    }
});
