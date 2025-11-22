<?php

declare(strict_types=1);

namespace App\Tests\Unit\Events\Rewards\Repository;

use App\Domain\Events\Rewards\Entity\Nomination;
use App\Domain\Events\Rewards\Repository\NominationQueryRepository;
use Closure;
use Database\Connection\ParamType;
use Database\Connection\ReadDatabaseInterface;
use Generator;
use Mockery;

it('get nominations for verification filter', function (array $rawDataFromDb): void {
    $connection = Mockery::mock(ReadDatabaseInterface::class);
    $repository = new NominationQueryRepository($connection, getDataMapper());

    $generator = function (array $data): Closure {
        return function () use ($data): Generator {
            foreach ($data as $item) {
                yield $item;
            }
        };
    };
    $programIds = [1, 2];
    $connection->shouldReceive('query')
        ->once()
        ->withArgs(function ($sql, array $params, array $types) use ($rawDataFromDb, $programIds): bool {
            return $params['program_id_list'] === $programIds
                && $types['program_id_list'] === ParamType::ARRAY_INTEGER;
        })
        ->andReturnUsing($generator($rawDataFromDb));

    $nominations = $repository->getNominationsForVerificationFilter($programIds);
    expect($nominations->count())->toBe(count($rawDataFromDb));
    foreach ($nominations as $nomination) {
        expect($nomination)->toBeInstanceOf(Nomination::class);
    }

})->with('nominations list');
