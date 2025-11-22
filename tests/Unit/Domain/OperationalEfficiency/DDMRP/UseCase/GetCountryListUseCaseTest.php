<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\OperationalEfficiency\DDMRP\UseCase;

use App\Domain\OperationalEfficiency\DDMRP\Repository\CountryQueryRepository;
use App\Domain\OperationalEfficiency\DDMRP\UseCase\GetCountryListUseCase;
use Illuminate\Support\Collection;
use Mockery;

beforeEach(function (): void {
    $this->repository = Mockery::mock(CountryQueryRepository::class);

    $this->useCase = new GetCountryListUseCase($this->repository);
});

afterEach(function (): void {
    Mockery::close();
});

it('returns country list from repository', function (): void {
    $countryCollection = new Collection();

    $this->repository->shouldReceive('getCountryList')
        ->once()
        ->andReturn($countryCollection);

    $result = $this->useCase->getCountyList();

    expect($result)->toBe($countryCollection);
});
