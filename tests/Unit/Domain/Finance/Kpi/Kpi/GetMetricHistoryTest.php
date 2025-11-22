<?php

declare(strict_types=1);

use App\Domain\Finance\Kpi\Entity\Kpi;
use App\Domain\Finance\Kpi\Repository\KpiQueryRepository;
use App\Domain\Finance\Kpi\UseCase\GetMetricHistoryUseCase;
use Illuminate\Support\Enumerable;

beforeEach(function (): void {
    $this->readMock = mock(KpiQueryRepository::class);
    $this->useCase = new GetMetricHistoryUseCase($this->readMock);
});

it('should return history with metrics for a given employee', function (): void {
    $empId = 1;

    $kpiMock1 = mock(Kpi::class);
    $kpiMock2 = mock(Kpi::class);

    $this->readMock->shouldReceive('getHistoryWithMetrics')
        ->with($empId)
        ->andReturn(collect([$kpiMock1, $kpiMock2]));

    $result = $this->useCase->getHistoryWithMetrics($empId);

    expect($result)->toBeInstanceOf(Enumerable::class);
    expect($result)->toHaveCount(2);
    expect($result->first())->toBe($kpiMock1);
    expect($result->last())->toBe($kpiMock2);
});

it('should return empty history if no data found', function (): void {
    $empId = 1;

    $this->readMock->shouldReceive('getHistoryWithMetrics')
        ->with($empId)
        ->andReturn(collect([]));

    $result = $this->useCase->getHistoryWithMetrics($empId);

    expect($result)->toBeInstanceOf(Enumerable::class);
    expect($result)->toHaveCount(0);
});

it('should call getHistoryWithMetrics with correct employee ID', function (): void {
    $empId = 1;

    $this->readMock->shouldReceive('getHistoryWithMetrics')
        ->with($empId)
        ->once()
        ->andReturn(collect([mock(Kpi::class)]));

    $this->useCase->getHistoryWithMetrics($empId);
});
