<?php

declare(strict_types=1);

use App\Domain\Events\Rewards\Entity\Nomination;
use App\Domain\Events\Rewards\Entity\Reward;
use App\Domain\Events\Rewards\Repository\RewardQueryRepository;
use App\Domain\Events\Rewards\UseCase\GetRewardsForVerificationFilterUseCase;
use Illuminate\Support\Collection;

beforeEach(function (): void {
    $this->repository = Mockery::mock(RewardQueryRepository::class);
    $this->useCase = new GetRewardsForVerificationFilterUseCase($this->repository);
});

afterEach(function (): void {
    Mockery::close();
});

test('getRewardsForVerificationFilter returns rewards from repository', function (): void {
    // Arrange
    $nominationIds = [1, 2, 3];
    $countryId = 10;

    $expectedReward1 = new Reward(
        id: 1,
        name: 'Test Reward 1',
        productId: 100,
        nomination: Mockery::mock(Nomination::class),
        commentary: 'Test Commentary 1',
        statuses: []
    );

    $expectedReward2 = new Reward(
        id: 2,
        name: 'Test Reward 2',
        productId: 200,
        nomination: Mockery::mock(Nomination::class),
        commentary: 'Test Commentary 2',
        statuses: []
    );

    $expectedRewards = new Collection([$expectedReward1, $expectedReward2]);

    // Mock the repository method
    $this->repository
        ->shouldReceive('getRewardsForVerificationFilter')
        ->once()
        ->with($nominationIds, $countryId)
        ->andReturn($expectedRewards);

    // Act
    $result = $this->useCase->getRewardsForVerificationFilter($nominationIds, $countryId);

    // Assert
    expect($result)->toHaveCount(2)
        ->and($result[0]->productId)->toBe(100)
        ->and($result[0]->name)->toBe('Test Reward 1')
        ->and($result[1]->productId)->toBe(200)
        ->and($result[1]->name)->toBe('Test Reward 2');
});

test('getRewardsForVerificationFilter handles empty result', function (): void {
    // Arrange
    $nominationIds = [4, 5];
    $countryId = 20;

    $emptyCollection = new Collection([]);

    // Mock the repository method
    $this->repository
        ->shouldReceive('getRewardsForVerificationFilter')
        ->once()
        ->with($nominationIds, $countryId)
        ->andReturn($emptyCollection);

    // Act
    $result = $this->useCase->getRewardsForVerificationFilter($nominationIds, $countryId);

    // Assert
    expect($result)->toBeEmpty();
});
