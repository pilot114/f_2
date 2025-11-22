<?php

declare(strict_types=1);

use App\Domain\Partners\SaleStructure\Entity\PartnerInfo;
use App\Domain\Partners\SaleStructure\Repository\PartnerInfoRepository;
use Database\Connection\ReadDatabaseInterface;

beforeEach(function (): void {
    // Создаем базовые данные для тестов
    $this->id = 1;
    $this->name = 'Test U. Ser';
    $this->contract = '11223344';
    $this->country_code = 2;
    $this->d_end = new DateTimeImmutable('2023-01-01 12:00:00');

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

dataset('employee data', [
    'single employee' => [
        [
            [
                'id,'           => 1,
                'name,'         => 'Test U. Ser',
                'contract,'     => '11223344',
                'country_code,' => 2,
                'd_end'         => null,
            ],
        ],
    ],
]);

dataset('empty result', [
    'no comments' => [[]],
]);

dataset('procedure result', [
    'o_result' => [[
        'o_result' => [
            'id'            => 1,
            'name'          => 'Test U. Ser',
            'contract'      => '11223344',
            'country_name'  => 'Testerstan',
            'rang'          => 1,
            'win_dt_career' => '2023-01-01 12:00:00',
        ],
    ]],
]);

dataset('rank name result', [
    'rank name' => [
        [
            [
                'name' => 'Test name',
            ],
        ],
    ],
]);

it('creates repository', function (): void {
    $repository = new PartnerInfoRepository(
        Mockery::mock(ReadDatabaseInterface::class),
        getDataMapper()
    );
    expect($repository)->toBeInstanceOf(PartnerInfoRepository::class);
});

it('retrieves employee data', function (array $rawDataFromDb): void {
    $connection = Mockery::mock(ReadDatabaseInterface::class);
    $repository = new PartnerInfoRepository($connection, getDataMapper());

    $contract = '11223344';

    $connection->shouldReceive('query')
        ->once()
        ->withArgs(function ($sql, array $params) use ($contract): bool {
            return $params['contract'] === $contract;
        })
        ->andReturnUsing(($this->genFromArray)($rawDataFromDb));

    $info = $repository->getEmployeeByContract($contract);

    expect($info)->toBeInstanceOf(PartnerInfo::class);
})->with('employee data');

it('retrieves no employee', function (array $rawDataFromDb): void {
    $connection = Mockery::mock(ReadDatabaseInterface::class);
    $repository = new PartnerInfoRepository($connection, getDataMapper());

    $contract = '11223344';

    $connection->shouldReceive('query')
        ->once()
        ->withArgs(function ($sql, array $params) use ($contract): bool {
            return true;
        })
        ->andReturnUsing(($this->genFromArray)($rawDataFromDb));

    $info = $repository->getEmployeeByContract($contract);

    expect($info)->toBeNull();

})->with('empty result');

it('retrieves employee info', function (array $rawDataFromDb): void {
    $connection = Mockery::mock(ReadDatabaseInterface::class);
    $repository = new PartnerInfoRepository($connection, getDataMapper());

    $id = 1;
    $connection->shouldReceive('procedure')
        ->once()
        ->withArgs(function ($sql, array $params) use ($id): bool {
            return true;
        })
        ->andReturn($rawDataFromDb);

    $info = $repository->getEmployeeInfo(1);
    expect($info['o_result']['id'])->toBe(1)
        ->and($info['o_result']['name'])->toBe('Test U. Ser')
        ->and($info['o_result']['contract'])->toBe('11223344')
        ->and($info['o_result']['country_name'])->toBe('Testerstan')
        ->and($info['o_result']['rang'])->toBe(1)
        ->and($info['o_result']['win_dt_career'])->toBe('2023-01-01 12:00:00');

})->with('procedure result');

it('retrieves rang name', function (array $rawDataFromDb): void {
    $connection = Mockery::mock(ReadDatabaseInterface::class);
    $repository = new PartnerInfoRepository($connection, getDataMapper());

    $rank = 42;

    $connection->shouldReceive('query')
        ->once()
        ->withArgs(function ($sql) use ($rank): bool {
            return true;
        })
        ->andReturnUsing(($this->genFromArray)($rawDataFromDb));

    $info = $repository->getRankNameById($rank);

    expect($info)->toBeString()
        ->toBe('Test name');

})->with('rank name result');
