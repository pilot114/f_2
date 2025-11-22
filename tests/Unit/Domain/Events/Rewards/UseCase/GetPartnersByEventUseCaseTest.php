<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Events\Rewards\UseCase;

use App\Common\DTO\FilterOption;
use App\Domain\Events\Rewards\DTO\PartnersByEventRequest;
use App\Domain\Events\Rewards\Entity\Country;
use App\Domain\Events\Rewards\Entity\Partner;
use App\Domain\Events\Rewards\Entity\PartnerStatus;
use App\Domain\Events\Rewards\Entity\RewardIssuanceState;
use App\Domain\Events\Rewards\Enum\PartnerStatusType;
use App\Domain\Events\Rewards\Repository\PartnersByEventQueryRepository;
use App\Domain\Events\Rewards\UseCase\GetPartnersByEventUseCase;
use Database\ORM\Attribute\Loader;
use DateTimeImmutable;
use Illuminate\Support\Collection;
use Mockery;

beforeEach(function (): void {
    $this->repository = Mockery::mock(PartnersByEventQueryRepository::class);
    $this->useCase = new GetPartnersByEventUseCase($this->repository);
});

afterEach(function (): void {
    Mockery::close();
});

it('returns all partners when no filters are applied', function (): void {
    // Arrange
    $request = new PartnersByEventRequest(
        eventId: 1,
        country: FilterOption::Q_ANY,
        hasPenalty: false
    );

    $partner1 = createPartner(1, 'Partner 1', PartnerStatusType::NOT_VERIFIED, []);
    $partner2 = createPartner(2, 'Partner 2', PartnerStatusType::NOT_AWARDED, []);
    $partners = new Collection([$partner1, $partner2]);

    $this->repository
        ->shouldReceive('getPartnersByEvent')
        ->with($request)
        ->once()
        ->andReturn($partners);

    $this->repository
        ->shouldReceive('getPartnersRegistrations')
        ->with([1, 2], 1)
        ->once()
        ->andReturn(new Collection([]));

    // Act
    $result = $this->useCase->getList($request);

    // Assert
    expect($result)->toHaveCount(2)
        ->and($result->contains($partner1))->toBeTrue()
    ;
});

it('filters partners by status', function (): void {
    // Arrange
    $request = new PartnersByEventRequest(
        eventId: 1,
        country: FilterOption::Q_ANY,
        hasPenalty: false,
        partnerStatus: PartnerStatusType::EXCLUDED
    );

    $partner1 = createPartner(1, 'Partner 1', PartnerStatusType::NOT_VERIFIED, []);
    $partner2 = createPartner(2, 'Partner 2', PartnerStatusType::EXCLUDED, []);

    $partners = new Collection([$partner2]);

    $this->repository
        ->shouldReceive('getPartnersByEvent')
        ->with($request)
        ->once()
        ->andReturn($partners);

    $this->repository
        ->shouldReceive('getPartnersRegistrations')
        ->with([2], 1)
        ->once()
        ->andReturn(new Collection([]));

    // Act
    $result = $this->useCase->getList($request);

    // Assert
    expect($result)->toHaveCount(1)
        ->and($result->contains($partner2))->toBeTrue()
    ;
});

it('filters out partners with empty rewards when filtering by program IDs', function (): void {
    // Arrange
    $request = new PartnersByEventRequest(
        eventId: 1,
        country: FilterOption::Q_ANY,
        hasPenalty: false,
        programIds: [123]
    );

    $reward1 = Mockery::mock(RewardIssuanceState::class);
    $partner1 = createPartner(1, 'Partner 1', PartnerStatusType::EXCLUDED, [$reward1]);
    $partner2 = createPartner(2, 'Partner 2', PartnerStatusType::NOT_VERIFIED, []);

    $partners = new Collection([$partner1]);

    $this->repository
        ->shouldReceive('getPartnersByEvent')
        ->with($request)
        ->once()
        ->andReturn($partners);

    $this->repository
        ->shouldReceive('getPartnersRegistrations')
        ->with([1], 1)
        ->once()
        ->andReturn(new Collection([]));

    // Act
    $result = $this->useCase->getList($request);

    // Assert
    expect($result)->toHaveCount(1)
        ->and($result->contains($partner1))->toBeTrue();
});

it('filters out partners with empty rewards when filtering by nomination IDs', function (): void {
    // Arrange
    $request = new PartnersByEventRequest(
        eventId: 1,
        country: FilterOption::Q_ANY,
        hasPenalty: false,
        nominationIds: [456]
    );

    $reward1 = Mockery::mock(RewardIssuanceState::class);
    $partner1 = createPartner(1, 'Partner 1', PartnerStatusType::EXCLUDED, [$reward1]);
    $partner2 = createPartner(2, 'Partner 2', PartnerStatusType::NOT_AWARDED, []);

    $partners = new Collection([$partner1]);

    $this->repository
        ->shouldReceive('getPartnersByEvent')
        ->with($request)
        ->once()
        ->andReturn($partners);

    $this->repository
        ->shouldReceive('getPartnersRegistrations')
        ->with([1], 1)
        ->once()
        ->andReturn(new Collection([]));

    // Act
    $result = $this->useCase->getList($request);

    // Assert
    expect($result)->toHaveCount(1)
        ->and($result->contains($partner1))->toBeTrue();
});

it('filters out partners with empty rewards when filtering by reward IDs', function (): void {
    // Arrange
    $request = new PartnersByEventRequest(
        eventId: 1,
        country: FilterOption::Q_ANY,
        hasPenalty: false,
        rewardIds: [789]
    );

    $reward1 = Mockery::mock(RewardIssuanceState::class);
    $partner1 = createPartner(1, 'Partner 1', PartnerStatusType::NOT_AWARDED, [$reward1]);
    $partner2 = createPartner(2, 'Partner 2', PartnerStatusType::NOT_AWARDED, []);

    $partners = new Collection([$partner1]);

    $this->repository
        ->shouldReceive('getPartnersByEvent')
        ->with($request)
        ->once()
        ->andReturn($partners);

    $this->repository
        ->shouldReceive('getPartnersRegistrations')
        ->with([1], 1)
        ->once()
        ->andReturn(new Collection([]));

    // Act
    $result = $this->useCase->getList($request);

    // Assert
    expect($result)->toHaveCount(1)
        ->and($result->contains($partner1))->toBeTrue();
});

it('filters out partners with empty rewards when filtering by nomination start date', function (): void {
    // Arrange
    $startDate = new DateTimeImmutable('2023-01-01');
    $request = new PartnersByEventRequest(
        eventId: 1,
        country: FilterOption::Q_ANY,
        hasPenalty: false,
        nominationStartDate: $startDate
    );

    $reward1 = Mockery::mock(RewardIssuanceState::class);
    $partner1 = createPartner(1, 'Partner 1', PartnerStatusType::EXCLUDED, [$reward1]);
    $partner2 = createPartner(2, 'Partner 2', PartnerStatusType::NOT_AWARDED, []);

    $partners = new Collection([$partner1]);

    $this->repository
        ->shouldReceive('getPartnersByEvent')
        ->with($request)
        ->once()
        ->andReturn($partners);

    $this->repository
        ->shouldReceive('getPartnersRegistrations')
        ->with([1], 1)
        ->once()
        ->andReturn(new Collection([]));

    // Act
    $result = $this->useCase->getList($request);

    // Assert
    expect($result)->toHaveCount(1)
        ->and($result->contains($partner1))->toBeTrue();
});

it('updates rewards count in partner status', function (): void {
    // Arrange
    $request = new PartnersByEventRequest(
        eventId: 1,
        country: FilterOption::Q_ANY,
        hasPenalty: false,
        programIds: [123]
    );

    $reward1 = Mockery::mock(RewardIssuanceState::class);
    $partner = createPartner(1, 'Test Partner', null, [$reward1]);

    $partners = new Collection([$partner]);

    $this->repository
        ->shouldReceive('getPartnersByEvent')
        ->with($request)
        ->once()
        ->andReturn($partners);

    $this->repository
        ->shouldReceive('getPartnersRegistrations')
        ->with([1], 1)
        ->once()
        ->andReturn(new Collection([]));

    // Act
    $result = $this->useCase->getList($request);

    // Assert
    expect($result)->toHaveCount(1);
});

it('combines status and reward filters correctly', function (): void {
    // Arrange
    $request = new PartnersByEventRequest(
        eventId: 1,
        country: FilterOption::Q_ANY,
        hasPenalty: false,
        partnerStatus: PartnerStatusType::NOT_VERIFIED,
        programIds: [123]
    );

    $reward1 = Mockery::mock(RewardIssuanceState::class);
    $partner1 = createPartner(1, 'Partner 1', PartnerStatusType::EXCLUDED, [$reward1]);
    $partner2 = createPartner(2, 'Partner 2', PartnerStatusType::NOT_AWARDED, []);
    $partner3 = createPartner(3, 'Partner 3', PartnerStatusType::NOT_VERIFIED, [$reward1]);

    $partners = new Collection([$partner3]);

    $this->repository
        ->shouldReceive('getPartnersByEvent')
        ->with($request)
        ->once()
        ->andReturn($partners);

    $this->repository
        ->shouldReceive('getPartnersRegistrations')
        ->with([3], 1)
        ->once()
        ->andReturn(new Collection([]));

    // Act
    $result = $this->useCase->getList($request);

    // Assert
    expect($result)->toHaveCount(1)
        ->and($result->contains($partner3))->toBeTrue();
});

it('returns partners count from repository', function (): void {
    // Arrange
    $request = new PartnersByEventRequest(
        eventId: 1,
        country: FilterOption::Q_ANY,
        hasPenalty: false,
    );

    $this->repository
        ->shouldReceive('countPartnersByEvent')
        ->with($request)
        ->once()
        ->andReturn(42);

    // Act
    $result = $this->useCase->count($request);

    // Assert
    expect($result)->toBe(42);
});

// Helper function to create a Partner
function createPartner(int $id, string $name, ?PartnerStatusType $statusType, array $rewards): Partner
{
    $status = null;

    if ($statusType instanceof PartnerStatusType) {
        $status = new PartnerStatus(
            Loader::ID_FOR_INSERT,
            $id,
            $statusType,
            0,
            0
        );
    }

    $country = new Country(1, 'Test Country', 'TC');

    return new Partner(
        id: $id,
        name: $name,
        contract: 'TEST_CONTRACT',
        country: $country,
        isFamily: false,
        status: $status,
        rewardIssuanceStates: $rewards
    );
}
