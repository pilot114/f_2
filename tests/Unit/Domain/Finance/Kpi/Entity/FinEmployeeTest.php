<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Finance\Kpi\Entity;

use App\Domain\Finance\Kpi\Entity\FinEmployee;

it('creates fin employee with all fields', function (): void {
    $employee = new FinEmployee(
        id: 1,
        cpId: 100,
        firstName: 'Иван',
        middleName: 'Иванович',
        lastName: 'Иванов',
    );

    expect($employee->getFinEmpId())->toBe(1)
        ->and($employee->getCpId())->toBe(100)
        ->and($employee->getFio())->toBe('Иванов Иван Иванович');
});

it('returns correct fin emp id', function (): void {
    $employee = new FinEmployee(
        id: 42,
        cpId: 200,
        firstName: 'Петр',
        middleName: 'Петрович',
        lastName: 'Петров',
    );

    expect($employee->getFinEmpId())->toBe(42);
});

it('returns correct cp id', function (): void {
    $employee = new FinEmployee(
        id: 10,
        cpId: 500,
        firstName: 'Сергей',
        middleName: 'Сергеевич',
        lastName: 'Сергеев',
    );

    expect($employee->getCpId())->toBe(500);
});

it('formats fio correctly', function (): void {
    $employee = new FinEmployee(
        id: 1,
        cpId: 1,
        firstName: 'Анна',
        middleName: 'Павловна',
        lastName: 'Смирнова',
    );

    expect($employee->getFio())->toBe('Смирнова Анна Павловна');
});

it('handles different names', function (): void {
    $employee = new FinEmployee(
        id: 5,
        cpId: 50,
        firstName: 'Александр',
        middleName: 'Владимирович',
        lastName: 'Козлов',
    );

    $fio = $employee->getFio();

    expect($fio)->toContain('Козлов')
        ->and($fio)->toContain('Александр')
        ->and($fio)->toContain('Владимирович');
});

it('fio has correct format with spaces', function (): void {
    $employee = new FinEmployee(
        id: 1,
        cpId: 1,
        firstName: 'First',
        middleName: 'Middle',
        lastName: 'Last',
    );

    expect($employee->getFio())->toBe('Last First Middle');
});

it('handles employee with short names', function (): void {
    $employee = new FinEmployee(
        id: 99,
        cpId: 999,
        firstName: 'A',
        middleName: 'B',
        lastName: 'C',
    );

    expect($employee->getFio())->toBe('C A B');
});
