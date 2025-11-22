<?php

declare(strict_types=1);

use App\Common\DTO\FilterOption;
use App\Domain\Events\Rewards\DTO\PartnersByContractsRequest;
use App\Domain\Events\Rewards\Entity\Country;
use App\Domain\Events\Rewards\Entity\Nomination;
use App\Domain\Events\Rewards\Entity\Partner;
use App\Domain\Events\Rewards\Entity\Program;
use App\Domain\Events\Rewards\Entity\Reward;
use App\Domain\Events\Rewards\Entity\RewardIssuanceState;
use App\Domain\Events\Rewards\Enum\RewardIssuanceStateStatusType;
use App\Domain\Events\Rewards\Repository\PartnersByContractQueryRepository;
use App\Domain\Events\Rewards\UseCase\GetPartnersByContractsUseCase;
use Illuminate\Support\Collection;

beforeEach(function (): void {
    $this->repository = Mockery::mock(PartnersByContractQueryRepository::class);
    $this->useCase = new GetPartnersByContractsUseCase($this->repository);
});

test('getList returns partners from repository', function (): void {
    // Arrange
    $request = new PartnersByContractsRequest(
        country: 1,
        contracts: ['123456', '789012'],
        programIds: [1, 2],
        nominationIds: [3, 4],
        rewardIds: [5, 6],
        rewardIssuanceState: RewardIssuanceStateStatusType::NOT_REWARDED,
        nominationStartDate: new DateTimeImmutable('2023-01-01'),
        nominationEndDate: new DateTimeImmutable('2023-12-31'),
        rewardStartDate: new DateTimeImmutable('2023-01-01'),
        rewardEndDate: new DateTimeImmutable('2023-12-31'),
        hideDeletedRewards: false
    );

    $program = new Program(1, 'Test Program');
    $nomination = new Nomination(1, 'Test Nomination', $program);
    $reward = new Reward(1, 'Test Reward', 100, $nomination);

    $rewardIssuanceState = new RewardIssuanceState(
        id: 1,
        calculationResultId: 100,
        program: $program,
        nomination: $nomination,
        rewardsCount: 1,
        status: RewardIssuanceStateStatusType::NOT_REWARDED,
        winDate: new DateTimeImmutable(),
        reward: $reward
    );

    $partner = new Partner(
        id: 1,
        name: 'Test Partner',
        contract: 'CONTRACT123',
        country: new Country(1, 'Country'),
        isFamily: false,
        rewardIssuanceStates: [
            1 => $rewardIssuanceState,
        ]
    );

    $expectedPartners = Collection::make([$partner]);

    // Assert that repository will be called with the correct request
    $this->repository
        ->shouldReceive('getWithActiveReward')
        ->once()
        ->with($request)
        ->andReturn($expectedPartners);

    $this->repository
        ->shouldReceive('getWithDeletedRewards')
        ->once()
        ->with($request)
        ->andReturn($expectedPartners);

    // Act
    $active = $this->useCase->getWithActiveRewards($request);
    $deleted = $this->useCase->getWithDeletedRewards($request);
    // Assert
    expect($active?->first()?->getFilteredRewardsCount())->toBe(1);
    expect($deleted?->first()?->getFilteredRewardsCount())->toBe(1);
});

test('getList returns empty collection when no partners found', function (): void {
    // Arrange
    $request = new PartnersByContractsRequest(
        country: FilterOption::Q_ANY,
        contracts: ['non-existent-contract']
    );

    $emptyCollection = Collection::make([]);

    // Assert that repository will be called with the correct request
    $this->repository
        ->shouldReceive('getWithActiveReward')
        ->once()
        ->with($request)
        ->andReturn($emptyCollection);

    // Act
    $result = $this->useCase->getWithActiveRewards($request);

    // Assert
    expect($result)->toBe($emptyCollection)
        ->and($result->count())->toBe(0);
});

test('getList passes all filter parameters correctly to repository', function (): void {
    // Arrange
    $request = new PartnersByContractsRequest(
        country: FilterOption::Q_ANY,
        contracts: ['abc123'],
        programIds: [10, 20],
        nominationIds: [30, 40],
        rewardIds: [50, 60],
        rewardIssuanceState: RewardIssuanceStateStatusType::REWARDED_FULL,
        nominationStartDate: new DateTimeImmutable('2024-01-01'),
        nominationEndDate: new DateTimeImmutable('2024-06-30'),
        rewardStartDate: new DateTimeImmutable('2024-07-01'),
        rewardEndDate: new DateTimeImmutable('2024-12-31')
    );

    $mockCollection = Collection::make([Mockery::mock(Partner::class)]);

    // Capture the actual request passed to repository for verification
    $this->repository
        ->shouldReceive('getWithActiveReward')
        ->once()
        ->withArgs(function ($actualRequest) use ($request): true {
            expect($actualRequest)->toBeInstanceOf(PartnersByContractsRequest::class)
                ->and($actualRequest->country)->toEqual($request->country)
                ->and($actualRequest->contracts)->toEqual($request->contracts)
                ->and($actualRequest->programIds)->toEqual($request->programIds)
                ->and($actualRequest->nominationIds)->toEqual($request->nominationIds)
                ->and($actualRequest->rewardIds)->toEqual($request->rewardIds)
                ->and($actualRequest->rewardIssuanceState)->toEqual($request->rewardIssuanceState)
                ->and($actualRequest->nominationStartDate)->toEqual($request->nominationStartDate)
                ->and($actualRequest->nominationEndDate)->toEqual($request->nominationEndDate)
                ->and($actualRequest->rewardStartDate)->toEqual($request->rewardStartDate)
                ->and($actualRequest->rewardEndDate)->toEqual($request->rewardEndDate);

            return true;
        })
        ->andReturn($mockCollection);

    // Act
    $result = $this->useCase->getWithActiveRewards($request);

    // Assert
    expect($result)->toBe($mockCollection);
});

test('getWithDeletedRewards returns partners with deleted rewards', function (): void {
    // Arrange
    $request = new PartnersByContractsRequest(
        country: FilterOption::Q_ANY,
        contracts: ['contract1', 'contract2'],
        withPenalties: false
    );

    $program = new Program(1, 'Test Program');
    $nomination = new Nomination(1, 'Test Nomination', $program);
    $reward = new Reward(1, 'Test Reward', 100, $nomination);

    $rewardIssuanceState = new RewardIssuanceState(
        id: 1,
        calculationResultId: 100,
        program: $program,
        nomination: $nomination,
        rewardsCount: 1,
        status: RewardIssuanceStateStatusType::NOT_REWARDED,
        winDate: new DateTimeImmutable(),
        reward: $reward,
        deleted: true
    );

    $partner = new Partner(
        id: 1,
        name: 'Test Partner',
        contract: 'CONTRACT123',
        country: new Country(1, 'Country'),
        isFamily: false,
        rewardIssuanceStates: [$rewardIssuanceState]
    );

    $expectedPartners = Collection::make([$partner]);

    // Assert that repository will be called with the correct request
    $this->repository
        ->shouldReceive('getWithDeletedRewards')
        ->once()
        ->with($request)
        ->andReturn($expectedPartners);

    // Act
    $result = $this->useCase->getWithDeletedRewards($request);

    // Assert
    expect($result?->first()?->getFilteredRewardsCount())->toBe(1);
});

afterEach(function (): void {
    Mockery::close();
});
