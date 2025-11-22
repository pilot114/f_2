<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Finance\KPI\UseCase;

use App\Domain\Finance\Kpi\Entity\KpiResponsible;
use App\Domain\Finance\Kpi\Entity\KpiResponsibleEnterprise;
use App\Domain\Finance\Kpi\Entity\KpiResponsibleUser;
use App\Domain\Finance\Kpi\Repository\KpiResponsibleQueryRepository;
use App\Domain\Finance\Kpi\UseCase\GetResponsibleListUseCase;
use DateTimeImmutable;
use Illuminate\Support\Collection;
use Mockery;

beforeEach(function (): void {
    $this->repository = Mockery::mock(KpiResponsibleQueryRepository::class);
    $this->useCase = new GetResponsibleListUseCase($this->repository);
});

afterEach(function (): void {
    Mockery::close();
});

test('getList returns list of KpiResponsible objects', function (): void {
    // Arrange
    $responsibles = new Collection([
        new KpiResponsible(
            id: 1,
            user: new KpiResponsibleUser(
                id: 101,
                name: 'John Doe',
                responseName: 'Manager'
            ),
            enterprise: new KpiResponsibleEnterprise(
                id: 201,
                name: 'ACME Inc.'
            ),
            changeDate: new DateTimeImmutable('2025-01-01'),
            changeUserId: 301
        ),
        new KpiResponsible(
            id: 2,
            user: new KpiResponsibleUser(
                id: 102,
                name: 'Jane Smith',
                responseName: 'Supervisor'
            ),
            enterprise: new KpiResponsibleEnterprise(
                id: 202,
                name: 'XYZ Corp'
            ),
            changeDate: new DateTimeImmutable('2025-01-02'),
            changeUserId: 302
        ),
    ]);

    // Mock repository response
    $this->repository->shouldReceive('getResponsibles')
        ->once()
        ->andReturn($responsibles);

    // Act
    $result = $this->useCase->getList();

    // Assert
    expect($result)->toBe($responsibles)
        ->and($result)->toHaveCount(2)
        ->and($result->first())->toBeInstanceOf(KpiResponsible::class)
        ->and($result->first()->id)->toBe(1)
        ->and($result->last()->id)->toBe(2);
});

test('getList returns empty collection when no responsibles exist', function (): void {
    // Arrange
    $emptyCollection = new Collection();

    // Mock repository response
    $this->repository->shouldReceive('getResponsibles')
        ->once()
        ->andReturn($emptyCollection);

    // Act
    $result = $this->useCase->getList();

    // Assert
    expect($result)->toBe($emptyCollection)
        ->and($result)->toBeEmpty();
});
