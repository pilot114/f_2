<?php

declare(strict_types=1);

use App\Common\DTO\FindItemResponse;
use App\Domain\Finance\Kpi\Entity\KpiMetric;
use App\Domain\Finance\Kpi\Repository\KpiMetricQueryRepository;
use App\Domain\Finance\Kpi\UseCase\GetMetricsUseCase;
use Illuminate\Support\Collection;

it('gets metrics with default parameters', function (): void {
    $repository = Mockery::mock(KpiMetricQueryRepository::class);
    $useCase = new GetMetricsUseCase($repository);

    $metrics = collect([
        new FindItemResponse(1, 'Metric 1'),
        new FindItemResponse(2, 'Metric 2'),
    ]);

    $repository->shouldReceive('getMetrics')
        ->with(null, false)
        ->andReturn($metrics->toArray());

    ##########################################
    $result = $useCase->getMetrics();
    ##########################################

    expect($result)->toBeArray()
        ->and(count($result))->toBe(2)
        ->and($result[0])->toBeInstanceOf(FindItemResponse::class);
});

it('gets metrics with search query and including disabled', function (): void {
    $repository = Mockery::mock(KpiMetricQueryRepository::class);
    $useCase = new GetMetricsUseCase($repository);

    $searchQuery = 'test';
    $withDisabled = true;

    $metrics = collect([
        new FindItemResponse(3, 'Test Metric'),
    ]);

    $repository->shouldReceive('getMetrics')
        ->with($searchQuery, $withDisabled)
        ->andReturn($metrics->toArray());

    ##########################################
    $result = $useCase->getMetrics($searchQuery, $withDisabled);
    ##########################################

    expect($result)->toBeArray()
        ->and(count($result))->toBe(1)
        ->and($result[0]->name)->toBe('Test Metric');
});

it('gets extended metrics with default parameters', function (): void {
    $repository = Mockery::mock(KpiMetricQueryRepository::class);
    $useCase = new GetMetricsUseCase($repository);

    $metricsExtended = collect([
        Mockery::mock(KpiMetric::class),
        Mockery::mock(KpiMetric::class),
    ]);

    $repository->shouldReceive('getMetricsExtended')
        ->with(null, null, null, false)
        ->andReturn($metricsExtended);

    ##########################################
    $result = $useCase->getMetricsExtended();
    ##########################################

    expect($result)->toBeInstanceOf(Collection::class)
        ->and($result->count())->toBe(2)
        ->and($result->first())->toBeInstanceOf(KpiMetric::class);
});

it('gets extended metrics with all parameters specified', function (): void {
    $repository = Mockery::mock(KpiMetricQueryRepository::class);
    $useCase = new GetMetricsUseCase($repository);

    $groupId = 1;
    $metricTypeId = 2;
    $searchQuery = 'test';
    $withDisabled = true;

    $metricsExtended = collect([
        Mockery::mock(KpiMetric::class),
    ]);

    $repository->shouldReceive('getMetricsExtended')
        ->with($groupId, $metricTypeId, $searchQuery, $withDisabled)
        ->andReturn($metricsExtended);

    ##########################################
    $result = $useCase->getMetricsExtended($groupId, $metricTypeId, $searchQuery, $withDisabled);
    ##########################################

    expect($result)->toBeInstanceOf(Collection::class)
        ->and($result->count())->toBe(1);
});
