<?php

declare(strict_types=1);

namespace App\Tests\Unit\Events\Rewards\Repository;

use App\Domain\Events\Rewards\Repository\CountryQueryRepository;
use Closure;
use Database\Connection\ReadDatabaseInterface;
use Database\EntityNotFoundDatabaseException;
use Generator;
use Illuminate\Support\Enumerable;
use Mockery;

it('get by ids', function (array $countryIds, array $rawDataFromDb, bool $missingCountry): void {
    $connection = Mockery::mock(ReadDatabaseInterface::class);
    $repository = new CountryQueryRepository($connection, getDataMapper());

    $generator = function (array $data): Closure {
        return function () use ($data): Generator {
            foreach ($data as $item) {
                yield $item;
            }
        };
    };

    $connection->shouldReceive('query')
        ->once()
        ->withArgs(function ($sql, array $params) use ($countryIds): bool {
            return $params['ids'] === $countryIds;
        })
        ->andReturnUsing($generator($rawDataFromDb));

    if ($missingCountry) {
        expect(fn (): Enumerable => $repository->getByIds($countryIds))->toThrow(EntityNotFoundDatabaseException::class);
        return;
    }

    $countries = $repository->getByIds($countryIds);
    expect($countries->count())->toBe(2);

})->with('country list');

it('find all', function (): void {
    $connection = Mockery::mock(ReadDatabaseInterface::class);
    $repository = new CountryQueryRepository($connection, getDataMapper());

    $generator = function (array $data): Closure {
        return function () use ($data): Generator {
            foreach ($data as $item) {
                yield $item;
            }
        };
    };

    $countriesRaw = [
        [
            'id'   => '1',
            'name' => 'Стана 1',
        ],
        [
            'id'   => '2',
            'name' => 'Босния и Герцеговина',
        ],
        [
            'id'   => '702',
            'name' => 'Босния и Герцеговина',
        ],
    ];

    $connection->shouldReceive('query')
        ->once()
        ->andReturnUsing($generator($countriesRaw));

    $filteredCountries = array_filter($countriesRaw, fn ($item): bool => $item['id'] !== '2');

    $countries = $repository->findAll();
    expect($countries->count())->toBe(count($filteredCountries));
});
