<?php

declare(strict_types=1);

use App\Domain\Finance\Kpi\Entity\CpDepartment;
use App\Domain\Finance\Kpi\Entity\Deputy;
use App\Domain\Finance\Kpi\Entity\DeputyUser;
use App\Domain\Finance\Kpi\Entity\KpiDepartmentState;
use App\Domain\Finance\Kpi\Repository\KpiQueryRepository;
use App\Domain\Finance\Kpi\UseCase\GetDeputyListUseCase;
use Illuminate\Support\Collection;
use Illuminate\Support\Enumerable;

it('retrieves a list of deputies for current user', function (): void {
    // Arrange
    $currentUserId = 123;

    // Mock deputies collection that would be returned by repository
    $mockDeputyCollection = new Collection([
        new Deputy(
            id: 1,
            currentUserId: $currentUserId,
            deputyUser: new DeputyUser(
                id: 456,
                name: 'John Doe',
                departments: [new CpDepartment(
                    id: 200,
                    name: 'Finance'
                )],
                positions: [
                    new KpiDepartmentState(
                        id: 100,
                        name: 'Manager',
                        isBoss: false,
                    ),
                ]
            ),
            dateStart: new DateTimeImmutable('2025-01-01'),
            dateEnd: new DateTimeImmutable('2025-12-31')
        ),
        new Deputy(
            id: 2,
            currentUserId: $currentUserId,
            deputyUser: new DeputyUser(
                id: 789,
                name: 'Jane Smith',
                departments: [new CpDepartment(
                    id: 201,
                    name: 'HR'
                )],
                positions: [
                    new KpiDepartmentState(
                        id: 101,
                        name: 'Team Lead',
                        isBoss: true,
                    ),
                ]
            ),
            dateStart: new DateTimeImmutable('2025-02-01'),
            dateEnd: new DateTimeImmutable('2025-11-30')
        ),
    ]);

    // Create a mock of KpiQueryRepository
    $mockRepository = Mockery::mock(KpiQueryRepository::class);

    // Set up the expectation for getDeputyList method call
    $mockRepository->shouldReceive('getDeputyList')
        ->once()
        ->with($currentUserId)
        ->andReturn($mockDeputyCollection);

    // Create the use case with mock repository
    $useCase = new GetDeputyListUseCase($mockRepository);

    // Act
    $result = $useCase->getList($currentUserId);

    // Assert
    expect($result)->toBeInstanceOf(Enumerable::class)
        ->and($result->count())->toBe(2);

    // Verify the first deputy details
    $firstDeputy = $result->first();
    expect($firstDeputy)->toBeInstanceOf(Deputy::class)
        ->and($firstDeputy->id)->toBe(1)
        ->and($firstDeputy->toArray()['deputyUser']['id'])->toBe(456)
        ->and($firstDeputy->toArray()['deputyUser']['name'])->toBe('John Doe')
        ->and($firstDeputy->toArray()['dateStart'])->toBeInstanceOf(DateTimeImmutable::class);

    // Verify the second deputy details
    $secondDeputy = $result->last();
    expect($secondDeputy)->toBeInstanceOf(Deputy::class)
        ->and($secondDeputy->id)->toBe(2)
        ->and($secondDeputy->toArray()['deputyUser']['id'])->toBe(789)
        ->and($secondDeputy->toArray()['deputyUser']['name'])->toBe('Jane Smith');
});

it('returns empty collection when no deputies exist', function (): void {
    // Arrange
    $currentUserId = 123;
    $emptyCollection = new Collection();

    $mockRepository = Mockery::mock(KpiQueryRepository::class);
    $mockRepository->shouldReceive('getDeputyList')
        ->once()
        ->with($currentUserId)
        ->andReturn($emptyCollection);

    $useCase = new GetDeputyListUseCase($mockRepository);

    // Act
    $result = $useCase->getList($currentUserId);

    // Assert
    expect($result)->toBeInstanceOf(Enumerable::class)
        ->and($result->count())->toBe(0)
        ->and($result->isEmpty())->toBeTrue();
});

// Cleanup Mockery after each test
afterEach(function (): void {
    Mockery::close();
});
