<?php

declare(strict_types=1);

use App\Domain\Finance\Kpi\Entity\KpiEmployee;
use App\Domain\Finance\Kpi\Repository\KpiQueryRepository;
use App\Domain\Finance\Kpi\UseCase\GetBossesUseCase;
use Illuminate\Support\Collection;

it('gets bosses with search query', function (): void {
    $repository = Mockery::mock(KpiQueryRepository::class);
    $useCase = new GetBossesUseCase($repository);

    $q = 'search term';
    $bossesCollection = collect([
        new KpiEmployee(1, 'John Doe', false),
        new KpiEmployee(2, 'Jane Smith', false),
    ]);

    $repository->shouldReceive('getBosses')
        ->with($q)
        ->andReturn($bossesCollection);

    ##########################################
    $result = $useCase->getBosses($q);
    ##########################################

    expect($result)->toBeInstanceOf(Collection::class)
        ->and($result)->toHaveCount(2);
});

it('gets all bosses when query is null', function (): void {
    $repository = Mockery::mock(KpiQueryRepository::class);
    $useCase = new GetBossesUseCase($repository);

    $q = null;
    $bossesCollection = collect([
        new KpiEmployee(1, 'John Doe', false),
        new KpiEmployee(2, 'Jane Smith', false),
        new KpiEmployee(3, 'Bob Johnson', false),
    ]);

    $repository->shouldReceive('getBosses')
        ->with($q)
        ->andReturn($bossesCollection);

    ##########################################
    $result = $useCase->getBosses($q);
    ##########################################

    expect($result)->toBeInstanceOf(Collection::class)
        ->and($result)->toHaveCount(3);
});
