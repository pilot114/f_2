<?php

declare(strict_types=1);

use App\Domain\Finance\Kpi\Entity\CpDepartment;
use App\Domain\Finance\Kpi\Repository\CpDepartmentQueryRepository;
use App\Domain\Finance\Kpi\UseCase\GetKpiDepartmentsUseCase;
use Illuminate\Support\Collection;

it('gets KPI departments', function (): void {
    $repository = Mockery::mock(CpDepartmentQueryRepository::class);
    $useCase = new GetKpiDepartmentsUseCase($repository);

    $departmentsCollection = collect([
        new CpDepartment(1, 'Finance'),
        new CpDepartment(2, 'HR'),
        new CpDepartment(3, 'IT'),
    ]);

    $repository->shouldReceive('getDepartments')
        ->withNoArgs()
        ->andReturn($departmentsCollection);

    ##########################################
    $result = $useCase->getDepartments();
    ##########################################

    expect($result)->toBeInstanceOf(Collection::class)
        ->and($result)->toHaveCount(3);
});

it('handles empty departments list', function (): void {
    $repository = Mockery::mock(CpDepartmentQueryRepository::class);
    $useCase = new GetKpiDepartmentsUseCase($repository);

    $repository->shouldReceive('getDepartments')
        ->withNoArgs()
        ->andReturn(collect([]));

    ##########################################
    $result = $useCase->getDepartments();
    ##########################################

    expect($result)->toBeInstanceOf(Collection::class)
        ->and($result)->toBeEmpty();
});
