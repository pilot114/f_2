<?php

declare(strict_types=1);

use App\Domain\Finance\Kpi\Repository\KpiCommandRepository;
use App\Domain\Finance\Kpi\Repository\KpiQueryRepository;
use App\Domain\Finance\Kpi\UseCase\AutoCompleteUseCase;

it('autocompletes KPI records for employees', function (): void {
    $readRepository = Mockery::mock(KpiQueryRepository::class);
    $writeRepository = Mockery::mock(KpiCommandRepository::class);
    $useCase = new AutoCompleteUseCase($readRepository, $writeRepository);

    $empId = 123;
    $q = 'search term';
    $onlyBoss = false;
    $finEmpIds = [123, 456, 789];

    $readRepository->shouldReceive('findEmpForExport')
        ->with($empId, $q, $onlyBoss)
        ->andReturn($finEmpIds);

    $writeRepository->shouldReceive('autoComplete')
        ->with($finEmpIds)
        ->andReturn(true);

    ##########################################
    $result = $useCase->autoComplete($empId, $q, $onlyBoss);
    ##########################################

    expect($result)->toBeTrue();
});

it('handles empty result from findEmpForExport', function (): void {
    $readRepository = Mockery::mock(KpiQueryRepository::class);
    $writeRepository = Mockery::mock(KpiCommandRepository::class);
    $useCase = new AutoCompleteUseCase($readRepository, $writeRepository);

    $empId = 123;
    $q = null;
    $onlyBoss = true;
    $finEmpIds = [];

    $readRepository->shouldReceive('findEmpForExport')
        ->with($empId, $q, $onlyBoss)
        ->andReturn($finEmpIds);

    $writeRepository->shouldReceive('autoComplete')
        ->with($finEmpIds)
        ->andReturn(false);

    ##########################################
    $result = $useCase->autoComplete($empId, $q, $onlyBoss);
    ##########################################

    expect($result)->toBeFalse();
});
