<?php

declare(strict_types=1);

use App\Domain\Finance\Kpi\Entity\KpiMetricGroup;
use App\Domain\Finance\Kpi\Repository\KpiMetricQueryRepository;
use App\Domain\Finance\Kpi\UseCase\GetMetricGroupsUseCase;
use Illuminate\Support\Collection;

it('gets metric groups', function (): void {
    $repository = Mockery::mock(KpiMetricQueryRepository::class);
    $useCase = new GetMetricGroupsUseCase($repository);

    $groupsCollection = collect([
        new KpiMetricGroup(1, 'Financial Metrics'),
        new KpiMetricGroup(2, 'Performance Metrics'),
    ]);

    $repository->shouldReceive('getMetricGroups')
        ->withNoArgs()
        ->andReturn($groupsCollection);

    ##########################################
    $result = $useCase->getMetricGroups();
    ##########################################

    expect($result)->toBeInstanceOf(Collection::class)
        ->and($result)->toHaveCount(2);
});

it('handles empty metric groups', function (): void {
    $repository = Mockery::mock(KpiMetricQueryRepository::class);
    $useCase = new GetMetricGroupsUseCase($repository);

    $repository->shouldReceive('getMetricGroups')
        ->withNoArgs()
        ->andReturn(collect([]));

    ##########################################
    $result = $useCase->getMetricGroups();
    ##########################################

    expect($result)->toBeInstanceOf(Collection::class)
        ->and($result)->toBeEmpty();
});
