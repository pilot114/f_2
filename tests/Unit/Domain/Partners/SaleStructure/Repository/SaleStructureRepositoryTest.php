<?php

declare(strict_types=1);

use App\Domain\Partners\SaleStructure\Repository\SaleStructureRepository;
use Database\Connection\ReadDatabaseInterface;

beforeEach(function (): void {
    $this->connection = Mockery::mock(ReadDatabaseInterface::class);
    $this->repository = new SaleStructureRepository($this->connection, getDataMapper());
    $this->genFromArray = function (array $data): Closure {
        return function () use ($data): Generator {
            foreach ($data as $item) {
                yield $item;
            }
        };
    };
});

afterEach(function (): void {
    Mockery::close();
});

dataset('country code result', [
    'country code' => [
        [
            [
                'country' => '42',
            ],
        ],
    ],
]);

dataset('currency result', [
    'currency' => [
        [
            [
                'name' => 'Test dollar',
            ],
        ],
    ],
]);

dataset('procedure result', [
    'procedure' => [
        [
            'pOut' => [
                [
                    'oo_percent' => '11',
                    'oo'         => '22',
                    'country'    => 'TESTERSTAN',
                    'dt'         => '2025-05',

                ],
                [
                    'oo_percent' => '33',
                    'oo'         => '44',
                    'country'    => 'UNTED STATES OF TESTERS',
                    'dt'         => '2025-05',
                ],
            ],
        ],
    ],
]);

dataset('procedure result with same currency', [
    'procedure' => [
        [
            'pOut' => [
                [
                    'oo_percent' => '11',
                    'oo'         => '22',
                    'country'    => 'TESTERSTAN',
                    'currency'   => 'testcoin',
                    'dt'         => '2025-05',

                ],
                [
                    'oo_percent' => '33',
                    'oo'         => '44',
                    'country'    => 'UNTED STATES OF TESTERS',
                    'currency'   => 'testcoin',
                    'dt'         => '2025-05',
                ],
            ],
        ],
    ],
]);

it('creates repository', function (): void {
    expect($this->repository)->toBeInstanceOf(SaleStructureRepository::class);
});

it('retrieves country code', function (array $rawDataFromDb): void {
    $countryName = 'test';

    $this->connection->shouldReceive('query')
        ->once()
        ->withArgs(function ($sql) use ($countryName): bool {
            return true;
        })
        ->andReturnUsing(($this->genFromArray)($rawDataFromDb));

    $info = $this->repository->getCountryCode($countryName);

    expect($info)->toBe(42);

})->with('country code result');

it('retrieves currency name', function (array $rawDataFromDb): void {
    $countryCode = 42;
    $this->connection->shouldReceive('query')
        ->once()
        ->withArgs(function ($sql) use ($countryCode): bool {
            return true;
        })
        ->andReturnUsing(($this->genFromArray)($rawDataFromDb));

    $info = $this->repository->getCurrencyNameByCountryCode($countryCode);

    expect($info)->toBe('Test dollar');

})->with('currency result');

it('retrieves sales structure from db', function (array $rawDataFromDb): void {
    $contract = '112233344';
    $this->connection->shouldReceive('procedure')
        ->once()
        ->withArgs(function ($name, array $paramsNames, array $paramsValues) use ($contract): bool {
            return true;
        })
        ->andReturn($rawDataFromDb);

    $date = new DateTimeImmutable();
    $info = $this->repository->getSaleStructure($contract, $date, $date);

    expect($info)->toBe($rawDataFromDb['pOut']);

})->with('procedure result');

it('retrieves sales structure from db with same currency', function (array $rawDataFromDb): void {
    $contract = '112233344';
    $this->connection->shouldReceive('procedure')
        ->once()
        ->withArgs(function ($name, array $paramsNames, array $paramsValues) use ($contract): bool {
            return true;
        })
        ->andReturn($rawDataFromDb);

    $date = new DateTimeImmutable();
    $info = $this->repository->getSaleStructure($contract, $date, $date);

    expect($info)->toBe($rawDataFromDb['pOut']);

})->with('procedure result');
