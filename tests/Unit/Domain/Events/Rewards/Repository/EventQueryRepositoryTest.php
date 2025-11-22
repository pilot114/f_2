<?php

declare(strict_types=1);

namespace App\Tests\Unit\Events\Rewards\Repository;

use App\Domain\Events\Rewards\Entity\Event;
use App\Domain\Events\Rewards\Repository\EventQueryRepository;
use Closure;
use Database\Connection\ReadDatabaseInterface;
use Database\EntityNotFoundDatabaseException;
use Generator;
use Mockery;

it('get events for verification filter', function (array $rawDataFromDb): void {
    $connection = Mockery::mock(ReadDatabaseInterface::class);
    $repository = new EventQueryRepository($connection, getDataMapper());

    $generator = function (array $data): Closure {
        return function () use ($data): Generator {
            foreach ($data as $item) {
                yield $item;
            }
        };
    };

    $connection->shouldReceive('query')
        ->once()
        ->andReturnUsing($generator($rawDataFromDb));

    $events = $repository->getEventsForVerificationFilter();
    expect($events->count())->toBe(count($rawDataFromDb));
    foreach ($events as $event) {
        expect($event)->toBeInstanceOf(Event::class);
    }

})->with('event list');

it('get event by id from allowed event list', function (array $rawDataFromDb): void {
    $connection = Mockery::mock(ReadDatabaseInterface::class);
    $repository = new EventQueryRepository($connection, getDataMapper());

    $generator = function (array $data): Closure {
        return function () use ($data): Generator {
            foreach ($data as $item) {
                yield $item;
            }
        };
    };
    $existingId = (int) $rawDataFromDb[0]['id'];
    $connection->shouldReceive('query')
        ->once()
        ->andReturnUsing($generator($rawDataFromDb));

    $event = $repository->getEventByIdFromAllowedList($existingId);
    expect($event)->toBeInstanceOf(Event::class);

})->with('event list');

it('get not allowed event from allowed event list', function (array $rawDataFromDb): void {
    $connection = Mockery::mock(ReadDatabaseInterface::class);
    $repository = new EventQueryRepository($connection, getDataMapper());

    $generator = function (array $data): Closure {
        return function () use ($data): Generator {
            foreach ($data as $item) {
                yield $item;
            }
        };
    };
    $notExistingId = PHP_INT_MAX;
    $connection->shouldReceive('query')
        ->once()
        ->andReturnUsing($generator($rawDataFromDb));

    expect(fn (): Event => $repository->getEventByIdFromAllowedList($notExistingId))
        ->toThrow(
            EntityNotFoundDatabaseException::class,
            "среди списка разрешенных не нашлось мероприятия с id = {$notExistingId}"
        );
})->with('event list');
