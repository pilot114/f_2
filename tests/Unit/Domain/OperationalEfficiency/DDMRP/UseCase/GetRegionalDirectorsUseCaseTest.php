<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\OperationalEfficiency\DDMRP\UseCase;

use App\Domain\OperationalEfficiency\DDMRP\Repository\EmployeeQueryRepository;
use App\Domain\OperationalEfficiency\DDMRP\UseCase\GetRegionalDirectorsUseCase;
use Illuminate\Support\Collection;
use Mockery;

beforeEach(function (): void {
    $this->repository = Mockery::mock(EmployeeQueryRepository::class);

    $this->useCase = new GetRegionalDirectorsUseCase($this->repository);
});

afterEach(function (): void {
    Mockery::close();
});

it('returns regional directors list from repository', function (): void {
    $directorsCollection = new Collection();

    $this->repository->shouldReceive('getRegionalDirectors')
        ->once()
        ->andReturn($directorsCollection);

    $result = $this->useCase->getRegionalDirectors();

    expect($result)->toBe($directorsCollection);
});
