<?php

declare(strict_types=1);

use App\Domain\Finance\Kpi\Entity\KpiDepartment;
use App\Domain\Finance\Kpi\Repository\KpiQueryRepository;
use App\Domain\Finance\Kpi\UseCase\GetListUseCase;
use Illuminate\Support\Collection;

it('gets KPI list for an employee', function (): void {
    $repository = Mockery::mock(KpiQueryRepository::class);
    $useCase = new GetListUseCase($repository);

    $empId = 123;
    $q = 'search term';
    $onlyBoss = false;
    $lastDateSend = new DateTimeImmutable('2023-01-15');

    $kpiCollection = collect([
        new KpiDepartment(1, 'Finance Department', 10, 1),
        new KpiDepartment(2, 'HR Department', 20, 1),
    ]);

    $repository->shouldReceive('getList')
        ->with($empId, $q, $onlyBoss)
        ->andReturn($kpiCollection);

    $repository->shouldReceive('lastDateSend')
        ->with($empId)
        ->andReturn($lastDateSend);

    ##########################################
    $result = $useCase->getList($empId, $q, $onlyBoss);
    ##########################################

    expect($result)->toBeArray()
        ->and($result[0])->toBeInstanceOf(Collection::class)
        ->and($result[0])->toHaveCount(2)
        ->and($result[1])->toBeInstanceOf(DateTimeImmutable::class);
});

it('handles empty KPI list', function (): void {
    $repository = Mockery::mock(KpiQueryRepository::class);
    $useCase = new GetListUseCase($repository);

    $empId = 456;
    $q = null;
    $onlyBoss = true;
    $lastDateSend = null;

    $repository->shouldReceive('getList')
        ->with($empId, $q, $onlyBoss)
        ->andReturn(collect([]));

    $repository->shouldReceive('lastDateSend')
        ->with($empId)
        ->andReturn($lastDateSend);

    ##########################################
    $result = $useCase->getList($empId, $q, $onlyBoss);
    ##########################################

    expect($result)->toBeArray()
        ->and($result[0])->toBeEmpty()
        ->and($result[1])->toBeNull();
});
