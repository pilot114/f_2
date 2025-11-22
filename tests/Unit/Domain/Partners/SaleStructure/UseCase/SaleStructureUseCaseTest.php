<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Partners\SaleStructure\UseCase;

use App\Domain\Partners\SaleStructure\Repository\SaleStructureRepository;
use App\Domain\Partners\SaleStructure\UseCase\SaleStructureUseCase;
use DateTimeImmutable;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

uses(MockeryPHPUnitIntegration::class);

beforeEach(function (): void {
    $this->repository = Mockery::mock(SaleStructureRepository::class);
    $this->useCase = new SaleStructureUseCase($this->repository);
});

it('returns empty array when no data found', function (): void {
    $contract = 'TEST123';
    $from = new DateTimeImmutable('2023-01-01');
    $till = new DateTimeImmutable('2023-12-31');

    $this->repository
        ->shouldReceive('getSaleStructure')
        ->with($contract, $from, $till)
        ->andReturn([]);

    $result = $this->useCase->get($contract, $from, $till);

    expect($result)->toBeArray()->toBeEmpty();
});

it('processes sale structure data correctly', function (): void {
    $contract = 'TEST123';
    $from = new DateTimeImmutable('2023-01-01');
    $till = new DateTimeImmutable('2023-12-31');

    $rawData = [
        [
            'dt'         => '2023-01-15',
            'country'    => 'РОССИЯ',
            'currency'   => 'RUB',
            'oo_percent' => '15.5',
            'oo'         => '1000.50',
        ],
        [
            'dt'         => '2023-02-15',
            'country'    => 'КАЗАХСТАН',
            'currency'   => 'KZT',
            'oo_percent' => '12.0',
            'oo'         => '500.00',
        ],
    ];

    $this->repository
        ->shouldReceive('getSaleStructure')
        ->with($contract, $from, $till)
        ->andReturn($rawData);

    $this->repository
        ->shouldReceive('getCountryCode')
        ->with('РОССИЯ')
        ->andReturn(1);

    $this->repository
        ->shouldReceive('getCountryCode')
        ->with('КАЗАХСТАН')
        ->andReturn(2);

    $result = $this->useCase->get($contract, $from, $till);

    expect($result)->toBeArray()
        ->and(count($result))->toBe(2);

    expect($result[0]['period'])->toBe('Февраль 2023')
        ->and($result[1]['period'])->toBe('Январь 2023');

    expect($result[0]['countries'])->toHaveCount(2)
        ->and($result[1]['countries'])->toHaveCount(2);
});

it('handles Thailand country name normalization', function (): void {
    $contract = 'TEST123';
    $from = new DateTimeImmutable('2023-01-01');
    $till = new DateTimeImmutable('2023-12-31');

    $rawData = [
        [
            'dt'         => '2023-01-15',
            'country'    => 'ТАЙЛАНД',
            'currency'   => 'THB',
            'oo_percent' => '10.0',
            'oo'         => '750.00',
        ],
    ];

    $this->repository
        ->shouldReceive('getSaleStructure')
        ->with($contract, $from, $till)
        ->andReturn($rawData);

    $this->repository
        ->shouldReceive('getCountryCode')
        ->with('ТАИЛАНД')
        ->andReturn(3);

    $result = $this->useCase->get($contract, $from, $till);

    expect($result[0]['countries'][0]->getName())->toBe('таиланд');
});

it('groups data by month correctly', function (): void {
    $contract = 'TEST123';
    $from = new DateTimeImmutable('2023-01-01');
    $till = new DateTimeImmutable('2023-12-31');

    $rawData = [
        [
            'dt'         => '2023-01-15',
            'country'    => 'РОССИЯ',
            'currency'   => 'RUB',
            'oo_percent' => '15.0',
            'oo'         => '1000.00',
        ],
        [
            'dt'         => '2023-01-20',
            'country'    => 'РОССИЯ',
            'currency'   => 'USD',
            'oo_percent' => '12.0',
            'oo'         => '800.00',
        ],
    ];

    $this->repository
        ->shouldReceive('getSaleStructure')
        ->with($contract, $from, $till)
        ->andReturn($rawData);

    $this->repository
        ->shouldReceive('getCountryCode')
        ->with('РОССИЯ')
        ->andReturn(1);

    $result = $this->useCase->get($contract, $from, $till);

    expect($result)->toHaveCount(1);
    expect($result[0]['period'])->toBe('Январь 2023');
    expect($result[0]['countries'])->toHaveCount(2);
});

it('enriches data with zero values for missing countries', function (): void {
    $contract = 'TEST123';
    $from = new DateTimeImmutable('2023-01-01');
    $till = new DateTimeImmutable('2023-12-31');

    $rawData = [
        [
            'dt'         => '2023-01-15',
            'country'    => 'РОССИЯ',
            'currency'   => 'RUB',
            'oo_percent' => '15.0',
            'oo'         => '1000.00',
        ],
        [
            'dt'         => '2023-02-15',
            'country'    => 'КАЗАХСТАН',
            'currency'   => 'KZT',
            'oo_percent' => '10.0',
            'oo'         => '500.00',
        ],
    ];

    $this->repository
        ->shouldReceive('getSaleStructure')
        ->with($contract, $from, $till)
        ->andReturn($rawData);

    $this->repository
        ->shouldReceive('getCountryCode')
        ->with('РОССИЯ')
        ->andReturn(1);

    $this->repository
        ->shouldReceive('getCountryCode')
        ->with('КАЗАХСТАН')
        ->andReturn(2);

    $result = $this->useCase->get($contract, $from, $till);

    expect($result)->toHaveCount(2);
    expect($result[0]['countries'])->toHaveCount(2);
    expect($result[1]['countries'])->toHaveCount(2);

    $januaryCountries = collect($result[1]['countries'])->keyBy(fn ($country) => $country->getName());
    expect($januaryCountries['казахстан']->getPoints())->toBe('0.0');
    expect($januaryCountries['казахстан']->getPercent())->toBe('0.0');
});

it('sorts countries by name and currency correctly', function (): void {
    $contract = 'TEST123';
    $from = new DateTimeImmutable('2023-01-01');
    $till = new DateTimeImmutable('2023-12-31');

    $rawData = [
        [
            'dt'         => '2023-01-15',
            'country'    => 'РОССИЯ',
            'currency'   => 'RUB',
            'oo_percent' => '15.0',
            'oo'         => '1000.00',
        ],
        [
            'dt'         => '2023-01-15',
            'country'    => 'РОССИЯ',
            'currency'   => 'USD',
            'oo_percent' => '10.0',
            'oo'         => '500.00',
        ],
        [
            'dt'         => '2023-01-15',
            'country'    => 'АРМЕНИЯ',
            'currency'   => 'AMD',
            'oo_percent' => '8.0',
            'oo'         => '300.00',
        ],
    ];

    $this->repository
        ->shouldReceive('getSaleStructure')
        ->with($contract, $from, $till)
        ->andReturn($rawData);

    $this->repository
        ->shouldReceive('getCountryCode')
        ->with('РОССИЯ')
        ->andReturn(1);

    $this->repository
        ->shouldReceive('getCountryCode')
        ->with('АРМЕНИЯ')
        ->andReturn(2);

    $result = $this->useCase->get($contract, $from, $till);

    $countries = $result[0]['countries'];
    expect($countries[0]->getName())->toBe('армения');
    expect($countries[1]->getName())->toBe('россия');
    expect($countries[1]->getCurrency())->toBe('USD');
    expect($countries[2]->getCurrency())->toBe('RUB');
});

it('returns results in reverse chronological order', function (): void {
    $contract = 'TEST123';
    $from = new DateTimeImmutable('2023-01-01');
    $till = new DateTimeImmutable('2023-12-31');

    $rawData = [
        [
            'dt'         => '2023-01-15',
            'country'    => 'РОССИЯ',
            'currency'   => 'RUB',
            'oo_percent' => '15.0',
            'oo'         => '1000.00',
        ],
        [
            'dt'         => '2023-03-15',
            'country'    => 'РОССИЯ',
            'currency'   => 'RUB',
            'oo_percent' => '12.0',
            'oo'         => '800.00',
        ],
    ];

    $this->repository
        ->shouldReceive('getSaleStructure')
        ->with($contract, $from, $till)
        ->andReturn($rawData);

    $this->repository
        ->shouldReceive('getCountryCode')
        ->with('РОССИЯ')
        ->andReturn(1);

    $result = $this->useCase->get($contract, $from, $till);

    expect($result[0]['period'])->toBe('Март 2023');
    expect($result[1]['period'])->toBe('Январь 2023');
});
