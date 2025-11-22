<?php

declare(strict_types=1);

use App\Domain\Finance\Kpi\Entity\KpiMetricType;
use App\Domain\Finance\Kpi\Enum\PaymentPlanType;
use App\Domain\Finance\Kpi\Repository\KpiMetricQueryRepository;
use App\Domain\Finance\Kpi\UseCase\GetMetricTypeUseCase;

it('gets metric type by id', function (): void {
    $repository = Mockery::mock(KpiMetricQueryRepository::class);
    $useCase = new GetMetricTypeUseCase($repository);

    $id = 1;
    $metricType = new KpiMetricType($id, 'Percentage', PaymentPlanType::LINEAR);

    $repository->shouldReceive('getMetricType')
        ->with($id)
        ->andReturn($metricType);

    ##########################################
    $result = $useCase->getMetricType($id);
    ##########################################

    expect($result)->toBeInstanceOf(KpiMetricType::class);
});

it('returns null when metric type not found', function (): void {
    $repository = Mockery::mock(KpiMetricQueryRepository::class);
    $useCase = new GetMetricTypeUseCase($repository);

    $id = 999;

    $repository->shouldReceive('getMetricType')
        ->with($id)
        ->andReturnNull();

    ##########################################
    $result = $useCase->getMetricType($id);
    ##########################################

    expect($result)->toBeNull();
});
