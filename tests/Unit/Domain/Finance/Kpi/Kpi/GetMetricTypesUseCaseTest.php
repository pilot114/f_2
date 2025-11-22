<?php

declare(strict_types=1);

use App\Common\DTO\FindItemResponse;
use App\Domain\Finance\Kpi\Entity\KpiMetricType;
use App\Domain\Finance\Kpi\Enum\PaymentPlanType;
use App\Domain\Finance\Kpi\Repository\KpiMetricQueryRepository;
use App\Domain\Finance\Kpi\UseCase\GetMetricTypesUseCase;
use Illuminate\Support\Collection;

it('gets metric types with search query', function (): void {
    $repository = Mockery::mock(KpiMetricQueryRepository::class);
    $useCase = new GetMetricTypesUseCase($repository);

    $q = 'search';
    $withDisabled = false;
    $typesCollection = collect([
        new FindItemResponse(1, 'Percentage'),
        new FindItemResponse(2, 'Currency'),
    ]);

    $repository->shouldReceive('getMetricTypes')
        ->with($q, $withDisabled)
        ->andReturn($typesCollection->toArray());

    ##########################################
    $result = $useCase->getMetricTypes($q, $withDisabled);
    ##########################################

    expect($result)->toBeArray()
        ->and($result)->toHaveCount(2);
});

it('gets metric types extended with disabled items', function (): void {
    $repository = Mockery::mock(KpiMetricQueryRepository::class);
    $useCase = new GetMetricTypesUseCase($repository);

    $q = null;
    $withDisabled = true;
    $typesCollection = collect([
        new KpiMetricType(1, 'Percentage', PaymentPlanType::LINEAR),
        new KpiMetricType(2, 'Currency', PaymentPlanType::RANGES),
    ]);

    $repository->shouldReceive('getMetricTypesExtends')
        ->with($q, $withDisabled)
        ->andReturn($typesCollection);

    ##########################################
    $result = $useCase->getMetricTypesExtended($q, $withDisabled);
    ##########################################

    expect($result)->toBeInstanceOf(Collection::class)
        ->and($result)->toHaveCount(2);
});

it('handles empty metric types result', function (): void {
    $repository = Mockery::mock(KpiMetricQueryRepository::class);
    $useCase = new GetMetricTypesUseCase($repository);

    $q = 'nonexistent';
    $withDisabled = false;

    $repository->shouldReceive('getMetricTypes')
        ->with($q, $withDisabled)
        ->andReturn([]);

    ##########################################
    $result = $useCase->getMetricTypes($q, $withDisabled);
    ##########################################

    expect($result)->toBeArray()
        ->and($result)->toBeEmpty();
});
