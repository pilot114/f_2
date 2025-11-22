<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Hr\Achievements\Entity;

use App\Domain\Hr\Achievements\Entity\Employee;

it('creates employee with all fields', function (): void {
    $employee = new Employee(
        id: 1,
        name: 'John Doe',
        positionName: 'Senior Developer',
    );

    expect($employee->id)->toBe(1);
});

it('converts to employee response', function (): void {
    $employee = new Employee(
        id: 5,
        name: 'Jane Smith',
        positionName: 'Team Lead',
    );

    $response = $employee->toEmployeeResponse();

    expect($response->id)->toBe(5)
        ->and($response->name)->toBe('Jane Smith')
        ->and($response->positionName)->toBe('Team Lead');
});

it('trims position name in response', function (): void {
    $employee = new Employee(
        id: 1,
        name: 'Test User',
        positionName: '  Manager  ',
    );

    $response = $employee->toEmployeeResponse();

    expect($response->positionName)->toBe('Manager');
});

it('handles cyrillic names', function (): void {
    $employee = new Employee(
        id: 1,
        name: 'Иван Иванов',
        positionName: 'Менеджер',
    );

    $response = $employee->toEmployeeResponse();

    expect($response->name)->toBe('Иван Иванов')
        ->and($response->positionName)->toBe('Менеджер');
});

it('handles empty position name', function (): void {
    $employee = new Employee(
        id: 1,
        name: 'Test',
        positionName: '',
    );

    $response = $employee->toEmployeeResponse();

    expect($response->positionName)->toBe('');
});

it('handles position name with only spaces', function (): void {
    $employee = new Employee(
        id: 1,
        name: 'Test',
        positionName: '   ',
    );

    $response = $employee->toEmployeeResponse();

    expect($response->positionName)->toBe('');
});
