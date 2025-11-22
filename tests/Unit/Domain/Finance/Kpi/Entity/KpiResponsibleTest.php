<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Finance\Kpi\Entity;

use App\Domain\Finance\Kpi\Entity\KpiResponsible;
use App\Domain\Finance\Kpi\Entity\KpiResponsibleEnterprise;
use App\Domain\Finance\Kpi\Entity\KpiResponsibleUser;
use DateTimeImmutable;

it('creates kpi responsible with all fields', function (): void {
    $user = new KpiResponsibleUser(id: 1, name: 'John Doe', responseName: 'Manager');
    $enterprise = new KpiResponsibleEnterprise(id: 10, name: 'Test Company');
    $date = new DateTimeImmutable('2024-01-01');

    $responsible = new KpiResponsible(
        id: 1,
        user: $user,
        enterprise: $enterprise,
        changeDate: $date,
        changeUserId: 5,
    );

    expect($responsible->getId())->toBe(1)
        ->and($responsible->id)->toBe(1);
});

it('returns correct id', function (): void {
    $user = new KpiResponsibleUser(id: 1, name: 'Test');
    $enterprise = new KpiResponsibleEnterprise(id: 1, name: 'Test');
    $date = new DateTimeImmutable();

    $responsible = new KpiResponsible(
        id: 42,
        user: $user,
        enterprise: $enterprise,
        changeDate: $date,
        changeUserId: 1,
    );

    expect($responsible->getId())->toBe(42);
});

it('updates responsible data', function (): void {
    $user = new KpiResponsibleUser(id: 1, name: 'Old User');
    $enterprise = new KpiResponsibleEnterprise(id: 1, name: 'Old Company');
    $date = new DateTimeImmutable('2024-01-01');

    $responsible = new KpiResponsible(
        id: 1,
        user: $user,
        enterprise: $enterprise,
        changeDate: $date,
        changeUserId: 5,
    );

    $responsible->update(enterpriseId: 20, userId: 30, currentUserId: 100);

    // Check that IDs were updated
    $result = $responsible->toArray();
    expect($result['user']['id'])->toBe(30)
        ->and($result['enterprise']['id'])->toBe(20);
});

it('converts to array', function (): void {
    $user = new KpiResponsibleUser(id: 5, name: 'Jane Smith', responseName: 'Director');
    $enterprise = new KpiResponsibleEnterprise(id: 15, name: 'ABC Corporation');
    $date = new DateTimeImmutable();

    $responsible = new KpiResponsible(
        id: 10,
        user: $user,
        enterprise: $enterprise,
        changeDate: $date,
        changeUserId: 3,
    );

    $result = $responsible->toArray();

    expect($result)->toHaveKeys(['id', 'user', 'enterprise'])
        ->and($result['id'])->toBe(10)
        ->and($result['user'])->toBeArray()
        ->and($result['user']['id'])->toBe(5)
        ->and($result['user']['name'])->toBe('Jane Smith')
        ->and($result['enterprise'])->toBeArray()
        ->and($result['enterprise']['id'])->toBe(15)
        ->and($result['enterprise']['name'])->toBe('ABC Corporation');
});

it('toArray structure is correct', function (): void {
    $user = new KpiResponsibleUser(id: 1, name: 'Test');
    $enterprise = new KpiResponsibleEnterprise(id: 1, name: 'Test');
    $date = new DateTimeImmutable();

    $responsible = new KpiResponsible(
        id: 1,
        user: $user,
        enterprise: $enterprise,
        changeDate: $date,
        changeUserId: 1,
    );

    $result = $responsible->toArray();

    expect($result)->toHaveCount(3);
});

it('handles cyrillic in user and enterprise names', function (): void {
    $user = new KpiResponsibleUser(id: 1, name: 'Иван Иванов', responseName: 'Менеджер');
    $enterprise = new KpiResponsibleEnterprise(id: 1, name: 'ООО "Компания"');
    $date = new DateTimeImmutable();

    $responsible = new KpiResponsible(
        id: 1,
        user: $user,
        enterprise: $enterprise,
        changeDate: $date,
        changeUserId: 1,
    );

    $result = $responsible->toArray();

    expect($result['user']['name'])->toBe('Иван Иванов')
        ->and($result['enterprise']['name'])->toBe('ООО "Компания"');
});
