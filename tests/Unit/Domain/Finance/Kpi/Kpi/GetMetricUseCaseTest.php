<?php

declare(strict_types=1);

use App\Domain\Finance\Kpi\Entity\KpiMetric;
use App\Domain\Finance\Kpi\Entity\KpiMetricGroup;
use App\Domain\Finance\Kpi\Entity\KpiMetricType;
use App\Domain\Finance\Kpi\Enum\KpiCalculationType;
use App\Domain\Finance\Kpi\Enum\KpiType;
use App\Domain\Finance\Kpi\Enum\UnitType;
use App\Domain\Finance\Kpi\Repository\KpiMetricQueryRepository;
use App\Domain\Finance\Kpi\UseCase\GetMetricUseCase;

it('gets metric by id', function (): void {
    $repository = Mockery::mock(KpiMetricQueryRepository::class);
    $useCase = new GetMetricUseCase($repository);

    $id = 1;
    $metric = new KpiMetric(
        id: $id,
        name: 'test',
        kpiType: KpiType::BIMONTHLY,
        calculationType: KpiCalculationType::AUTO,
        calculationTypeDescription: 'desc',
        unitType: UnitType::PERCENTS,
        group: Mockery::mock(KpiMetricGroup::class),
        type: Mockery::mock(KpiMetricType::class),
    );

    $repository->shouldReceive('getMetric')
        ->with($id)
        ->andReturn($metric);

    ##########################################
    $result = $useCase->getMetric($id);
    ##########################################

    expect($result)->toBeInstanceOf(KpiMetric::class)
        ->and($result->id)->toBe($id);
});

it('returns null when metric not found', function (): void {
    $repository = Mockery::mock(KpiMetricQueryRepository::class);
    $useCase = new GetMetricUseCase($repository);

    $id = 999;

    $repository->shouldReceive('getMetric')
        ->with($id)
        ->andReturnNull();

    ##########################################
    $result = $useCase->getMetric($id);
    ##########################################

    expect($result)->toBeNull();
});
