<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Events\Rewards\UseCase;

use App\Domain\Events\Rewards\DTO\PartnerFullInfoRequest;
use App\Domain\Events\Rewards\Entity\Country;
use App\Domain\Events\Rewards\Entity\Partner;
use App\Domain\Events\Rewards\Enum\RewardIssuanceStateStatusType;
use App\Domain\Events\Rewards\Repository\PartnersByEventQueryRepository;
use App\Domain\Events\Rewards\Repository\PartnersFullInfoQueryRepository;
use App\Domain\Events\Rewards\UseCase\GetPartnerFullInfoUseCase;
use DateTimeImmutable;
use Illuminate\Support\Collection;
use Mockery;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

beforeEach(function (): void {
    $this->repository = Mockery::mock(PartnersFullInfoQueryRepository::class);
    $this->byEventRepository = Mockery::mock(PartnersByEventQueryRepository::class);
    $this->useCase = new GetPartnerFullInfoUseCase($this->repository, $this->byEventRepository);
});

afterEach(function (): void {
    Mockery::close();
});

it('gets full partner info', function (): void {
    // Arrange
    $partnerId = 12345;
    $programIds = [1, 2, 3];
    $nominationIds = [10, 20];
    $rewardIds = [100, 200];
    $rewardIssuanceState = RewardIssuanceStateStatusType::REWARDED_FULL;
    $nominationStartDate = new DateTimeImmutable('2024-01-01');
    $nominationEndDate = new DateTimeImmutable('2024-12-31');
    $rewardStartDate = new DateTimeImmutable('2024-01-15');
    $rewardEndDate = new DateTimeImmutable('2024-12-15');
    $hasPenalty = true;

    $request = new PartnerFullInfoRequest(
        partnerId: $partnerId,
        programIds: $programIds,
        nominationIds: $nominationIds,
        rewardIds: $rewardIds,
        rewardIssuanceState: $rewardIssuanceState,
        nominationStartDate: $nominationStartDate,
        nominationEndDate: $nominationEndDate,
        rewardStartDate: $rewardStartDate,
        rewardEndDate: $rewardEndDate,
        hasPenalty: $hasPenalty,
        eventId: 1
    );

    $expectedPartner = new Partner(
        id: $partnerId,
        name: 'Test Partner',
        contract: 'TEST_CONTRACT',
        country: new Country(1, 'Test Country', 'TC'),
        isFamily: false
    );

    // Assert
    $this->repository
        ->shouldReceive('getWithActiveRewards')
        ->once()
        ->with($request)
        ->andReturn($expectedPartner);

    $this->byEventRepository
        ->shouldReceive('getPartnersRegistrations')
        ->once()
        ->with([$partnerId], 1)
        ->andReturn(new Collection([]));

    //  Act
    $result = $this->useCase->getPartnerFullInfo($request);

    // Assert
    expect($result)->toBe($expectedPartner);
    expect($result->id)->toBe($partnerId);
});

it('gets full partner info with deleted', function (): void {
    // Arrange
    $partnerId = 12345;
    $programIds = [1, 2, 3];
    $nominationIds = [10, 20];
    $rewardIds = [100, 200];
    $rewardIssuanceState = RewardIssuanceStateStatusType::REWARDED_FULL;
    $nominationStartDate = new DateTimeImmutable('2024-01-01');
    $nominationEndDate = new DateTimeImmutable('2024-12-31');
    $rewardStartDate = new DateTimeImmutable('2024-01-15');
    $rewardEndDate = new DateTimeImmutable('2024-12-15');
    $hasPenalty = true;

    $request = new PartnerFullInfoRequest(
        partnerId: $partnerId,
        programIds: $programIds,
        nominationIds: $nominationIds,
        rewardIds: $rewardIds,
        rewardIssuanceState: $rewardIssuanceState,
        nominationStartDate: $nominationStartDate,
        nominationEndDate: $nominationEndDate,
        rewardStartDate: $rewardStartDate,
        rewardEndDate: $rewardEndDate,
        hasPenalty: $hasPenalty,
        hideDeletedRewards: false,
        eventId: 1
    );

    $expectedPartner = new Partner(
        id: $partnerId,
        name: 'Test Partner',
        contract: 'TEST_CONTRACT',
        country: new Country(1, 'Test Country', 'TC'),
        isFamily: false
    );

    $this->repository
        ->shouldReceive('getWithDeletedRewards')
        ->once()
        ->with($request)
        ->andReturn($expectedPartner);

    $this->byEventRepository
        ->shouldReceive('getPartnersRegistrations')
        ->once()
        ->with([$partnerId], 1)
        ->andReturn(new Collection([]));

    //  Act
    $result = $this->useCase->getPartnerFullInfo($request);

    // Assert
    expect($result)->toBe($expectedPartner);
    expect($result->id)->toBe($partnerId);
});

it('passes through any exceptions thrown by the repository', function (): void {
    //  Arrange
    $request = new PartnerFullInfoRequest(partnerId: 999);
    $expectedException = new NotFoundHttpException('не найден партнёр с id 999');

    $this->repository
        ->shouldReceive('getWithActiveRewards')
        ->once()
        ->with($request)
        ->andThrow($expectedException);

    // Act & Assert
    expect(fn () => $this->useCase->getPartnerFullInfo($request))
        ->toThrow(NotFoundHttpException::class, 'не найден партнёр с id 999');
});

it('correctly handles optional parameters', function (): void {
    // Arrange
    $partnerId = 12345;

    // Request with only required parameters
    $request = new PartnerFullInfoRequest(partnerId: $partnerId, eventId: 1);

    $expectedPartner = new Partner(
        id: $partnerId,
        name: 'Test Partner',
        contract: 'TEST_CONTRACT',
        country: new Country(1, 'Test Country', 'TC'),
        isFamily: false
    );

    // Assert
    $this->repository
        ->shouldReceive('getWithActiveRewards')
        ->once()
        ->with($request)
        ->andReturn($expectedPartner);

    $this->byEventRepository
        ->shouldReceive('getPartnersRegistrations')
        ->once()
        ->with([$partnerId], 1)
        ->andReturn(new Collection([]));

    // Act
    $result = $this->useCase->getPartnerFullInfo($request);

    // Assert
    expect($result)->toBe($expectedPartner);
    expect($result->id)->toBe($partnerId);
});
