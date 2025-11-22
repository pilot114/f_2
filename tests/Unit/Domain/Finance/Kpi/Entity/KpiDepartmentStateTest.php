<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Finance\Kpi\Entity;

use App\Domain\Finance\Kpi\Entity\KpiDepartmentState;

it('creates kpi department state with all fields', function (): void {
    $state = new KpiDepartmentState(
        id: 1,
        name: 'Active',
        isBoss: true,
    );

    expect($state->id)->toBe(1)
        ->and($state->getName())->toBe('Active')
        ->and($state->isBoss())->toBeTrue();
});

it('returns correct name', function (): void {
    $state = new KpiDepartmentState(
        id: 2,
        name: 'Pending',
        isBoss: false,
    );

    expect($state->getName())->toBe('Pending');
});

it('returns correct boss status when true', function (): void {
    $state = new KpiDepartmentState(
        id: 3,
        name: 'Head',
        isBoss: true,
    );

    expect($state->isBoss())->toBeTrue();
});

it('returns correct boss status when false', function (): void {
    $state = new KpiDepartmentState(
        id: 4,
        name: 'Employee',
        isBoss: false,
    );

    expect($state->isBoss())->toBeFalse();
});

it('handles cyrillic characters in name', function (): void {
    $state = new KpiDepartmentState(
        id: 5,
        name: 'Руководитель отдела',
        isBoss: true,
    );

    expect($state->getName())->toBe('Руководитель отдела');
});

it('handles different state names', function (string $stateName): void {
    $state = new KpiDepartmentState(
        id: 1,
        name: $stateName,
        isBoss: false,
    );

    expect($state->getName())->toBe($stateName);
})->with([
    'Manager',
    'Supervisor',
    'Team Lead',
    'Director',
]);

it('handles different boss flags', function (bool $isBoss): void {
    $state = new KpiDepartmentState(
        id: 1,
        name: 'State',
        isBoss: $isBoss,
    );

    expect($state->isBoss())->toBe($isBoss);
})->with([true, false]);

it('id is readonly', function (): void {
    $state = new KpiDepartmentState(
        id: 100,
        name: 'Test',
        isBoss: true,
    );

    expect($state->id)->toBe(100);
});
