<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\OperationalEfficiency\DDMRP\UseCase;

use App\Domain\OperationalEfficiency\DDMRP\DTO\GetCokListRequest;
use App\Domain\OperationalEfficiency\DDMRP\Repository\CokQueryRepository;
use App\Domain\OperationalEfficiency\DDMRP\UseCase\GetCokListUseCase;
use Illuminate\Support\Collection;
use Mockery;

beforeEach(function (): void {
    $this->repository = Mockery::mock(CokQueryRepository::class);

    $this->useCase = new GetCokListUseCase($this->repository);
});

afterEach(function (): void {
    Mockery::close();
});

it('returns cok list from repository', function (): void {
    $request = new GetCokListRequest(
        countryId: 1,
        regionDirectorId: 100,
        search: 'test'
    );

    $cokCollection = new Collection();

    $this->repository->shouldReceive('getCokList')
        ->once()
        ->with($request)
        ->andReturn($cokCollection);

    $result = $this->useCase->getCokList($request);

    expect($result)->toBe($cokCollection);
});
