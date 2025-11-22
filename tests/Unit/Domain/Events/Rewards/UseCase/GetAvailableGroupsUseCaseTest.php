<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Events\Rewards\UseCase;

use App\Domain\Events\Rewards\Entity\Group;
use App\Domain\Events\Rewards\Repository\GroupQueryRepository;
use App\Domain\Events\Rewards\UseCase\GetAvailableGroupsUseCase;
use Illuminate\Support\Collection;
use Mockery;

beforeEach(function (): void {
    $this->repository = Mockery::mock(GroupQueryRepository::class);
    $this->useCase = new GetAvailableGroupsUseCase($this->repository);
});

afterEach(function (): void {
    Mockery::close();
});

it('returns available groups from repository', function (): void {
    // Arrange
    $mockGroups = new Collection([
        new Group(1, 'Group 1'),
        new Group(2, 'Group 2'),
        new Group(3, 'Group 3'),
    ]);

    $this->repository
        ->shouldReceive('getAvailableGroups')
        ->once()
        ->andReturn($mockGroups);

    // Act
    $result = $this->useCase->getAvailableGroups();

    // Assert
    expect($result)->toBe($mockGroups)
        ->and($result)->toHaveCount(3)
        ->and($result->first())->toBeInstanceOf(Group::class)
        ->and($result->first()->getName())->toBe('Group 1');
});

it('returns empty collection when no groups are available', function (): void {
    // Arrange
    $emptyCollection = new Collection([]);

    $this->repository
        ->shouldReceive('getAvailableGroups')
        ->once()
        ->andReturn($emptyCollection);

    // Act
    $result = $this->useCase->getAvailableGroups();

    // Assert
    expect($result)->toBe($emptyCollection)
        ->and($result)->toBeEmpty();
});

it('passes through the repository response unchanged', function (): void {
    // Arrange
    $mockGroup = new Group(42, 'Test Group');
    $collection = new Collection([$mockGroup]);

    $this->repository
        ->shouldReceive('getAvailableGroups')
        ->once()
        ->andReturn($collection);

    // Act
    $result = $this->useCase->getAvailableGroups();

    // Assert
    expect($result)->toBe($collection)
        ->and($result->first()->id)->toBe(42)
        ->and($result->first()->getName())->toBe('Test Group');
});
