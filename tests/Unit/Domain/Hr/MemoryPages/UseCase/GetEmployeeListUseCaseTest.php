<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Hr\MemoryPages\UseCase;

use App\Domain\Hr\MemoryPages\DTO\GetEmployeeListRequest;
use App\Domain\Hr\MemoryPages\Entity\Employee;
use App\Domain\Hr\MemoryPages\Repository\EmployeeQueryRepository;
use App\Domain\Hr\MemoryPages\UseCase\GetEmployeeListUseCase;
use Illuminate\Support\Collection;
use Mockery;

afterEach(function (): void {
    Mockery::close();
});

it('gets list of employees', function (): void {
    // Arrange
    $repository = Mockery::mock(EmployeeQueryRepository::class);
    $useCase = new GetEmployeeListUseCase($repository);

    $employee1 = new Employee(id: 1, name: 'User 1', response: []);
    $employee2 = new Employee(id: 2, name: 'User 2', response: []);
    $employee3 = new Employee(id: 3, name: 'User 3', response: []);

    $request = new GetEmployeeListRequest(search: 'User');
    $employees = collect([$employee1, $employee2, $employee3]);

    $repository->shouldReceive('getList')
        ->with($request)
        ->once()
        ->andReturn($employees);

    // Act
    $result = $useCase->getList($request);

    // Assert
    expect($result)->toBeInstanceOf(Collection::class)
        ->and($result)->toHaveCount(3)
        ->and($result)->toContain($employee1, $employee2, $employee3);
});

it('returns empty collection when no employees found', function (): void {
    // Arrange
    $repository = Mockery::mock(EmployeeQueryRepository::class);
    $useCase = new GetEmployeeListUseCase($repository);

    $request = new GetEmployeeListRequest(search: 'NonExistent');
    $emptyCollection = collect([]);

    $repository->shouldReceive('getList')
        ->with($request)
        ->once()
        ->andReturn($emptyCollection);

    // Act
    $result = $useCase->getList($request);

    // Assert
    expect($result)->toBeEmpty();
});
