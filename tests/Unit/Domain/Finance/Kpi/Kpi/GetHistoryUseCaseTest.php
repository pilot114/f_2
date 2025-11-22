<?php

declare(strict_types=1);

use App\Domain\Finance\Kpi\Entity\Kpi;
use App\Domain\Finance\Kpi\Enum\KpiType;
use App\Domain\Finance\Kpi\Repository\KpiQueryRepository;
use App\Domain\Finance\Kpi\Repository\KpiResponsibleQueryRepository;
use App\Domain\Finance\Kpi\UseCase\GetHistoryUseCase;
use Illuminate\Support\Collection;

it('gets KPI history for an employee', function (): void {
    $repository = Mockery::mock(KpiQueryRepository::class);
    $repository2 = Mockery::mock(KpiResponsibleQueryRepository::class);
    $useCase = new GetHistoryUseCase($repository, $repository2);

    $empId = 123;
    $kpiCollection = collect([
        new Kpi(1, new DateTimeImmutable('01.01.2020'), KpiType::MONTHLY, 10, 1),
        new Kpi(2, new DateTimeImmutable('01.01.2010'), KpiType::BIMONTHLY, 50, 1),
    ]);

    $repository->shouldReceive('getHistory')
        ->with($empId)
        ->andReturn($kpiCollection);

    $repository2->shouldReceive('getActualResponsible')
        ->once();

    ##########################################
    $result = $useCase->getHistory($empId);
    ##########################################

    expect($result[0])->toBeInstanceOf(Collection::class)
        ->and($result[0])->toHaveCount(2);
});

it('handles empty history for an employee', function (): void {
    $repository = Mockery::mock(KpiQueryRepository::class);
    $repository2 = Mockery::mock(KpiResponsibleQueryRepository::class);
    $useCase = new GetHistoryUseCase($repository, $repository2);

    $empId = 456;
    $emptyCollection = collect([]);

    $repository->shouldReceive('getHistory')
        ->with($empId)
        ->andReturn($emptyCollection);

    $repository2->shouldReceive('getActualResponsible')
        ->once();

    ##########################################
    $result = $useCase->getHistory($empId);
    ##########################################

    expect($result[0])->toBeInstanceOf(Collection::class)
        ->and($result[0])->toBeEmpty();
});
