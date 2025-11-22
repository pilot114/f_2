<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\OperationalEfficiency\DDMRP\Entity;

use App\Domain\OperationalEfficiency\DDMRP\Entity\CokEmployee;
use App\Domain\OperationalEfficiency\DDMRP\Entity\DdmrpEmployeeAccess;
use App\Domain\OperationalEfficiency\DDMRP\Entity\Response;

it('creates cok employee with basic fields', function (): void {
    $employee = new CokEmployee(
        id: 1,
        cokContract: 'TEST-001',
        departmentId: 100,
        name: 'John Doe',
        response: null,
        email: 'john@test.com',
        phone: '+1234567890',
        accessToDdmrp: null
    );

    expect($employee->getId())->toBe(1)
        ->and($employee->getCokContract())->toBe('TEST-001')
        ->and($employee->departmentId)->toBe(100);
});

it('returns null when no access to ddmrp', function (): void {
    $employee = new CokEmployee(
        id: 1,
        cokContract: 'TEST-002',
        departmentId: 100,
        name: 'Jane Doe',
        response: null,
        email: 'jane@test.com',
        phone: null,
        accessToDdmrp: null
    );

    expect($employee->getAccessToDdmrp())->toBeNull();
});

it('returns access when employee has ddmrp access', function (): void {
    $access = new DdmrpEmployeeAccess(
        id: 50,
        contract: 'TEST-003',
        cpEmpId: 1
    );

    $employee = new CokEmployee(
        id: 1,
        cokContract: 'TEST-003',
        departmentId: 100,
        name: 'Bob Smith',
        response: null,
        email: 'bob@test.com',
        phone: null,
        accessToDdmrp: $access
    );

    expect($employee->getAccessToDdmrp())->toBe($access);
});

it('converts to CokEmployeesResponse without access', function (): void {
    $employee = new CokEmployee(
        id: 1,
        cokContract: 'TEST-004',
        departmentId: 100,
        name: 'Alice Johnson',
        response: null,
        email: 'alice@test.com',
        phone: '+9876543210',
        accessToDdmrp: null
    );

    $response = $employee->toCokEmployeesResponse();

    expect($response->id)->toBe(1)
        ->and($response->name)->toBe('Alice Johnson')
        ->and($response->email)->toBe('alice@test.com')
        ->and($response->phone)->toBe('+9876543210')
        ->and($response->response)->toBeNull()
        ->and($response->accessToDddmrp)->toBeFalse();
});

it('converts to CokEmployeesResponse with access', function (): void {
    $access = new DdmrpEmployeeAccess(
        id: 50,
        contract: 'TEST-005',
        cpEmpId: 1
    );

    $employee = new CokEmployee(
        id: 1,
        cokContract: 'TEST-005',
        departmentId: 100,
        name: 'Charlie Brown',
        response: null,
        email: 'charlie@test.com',
        phone: null,
        accessToDdmrp: $access
    );

    $response = $employee->toCokEmployeesResponse();

    expect($response->id)->toBe(1)
        ->and($response->name)->toBe('Charlie Brown')
        ->and($response->email)->toBe('charlie@test.com')
        ->and($response->accessToDddmrp)->toBeTrue();
});

it('converts to CokEmployeesResponse with response', function (): void {
    $responseEntity = new Response(
        id: 1,
        name: 'Manager'
    );

    $employee = new CokEmployee(
        id: 1,
        cokContract: 'TEST-006',
        departmentId: 100,
        name: 'David Wilson',
        response: $responseEntity,
        email: 'david@test.com',
        phone: null,
        accessToDdmrp: null
    );

    $response = $employee->toCokEmployeesResponse();

    expect($response->id)->toBe(1)
        ->and($response->name)->toBe('David Wilson')
        ->and($response->response)->not->toBeNull()
        ->and($response->response->id)->toBe(1)
        ->and($response->response->name)->toBe('Manager');
});
