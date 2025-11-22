<?php

declare(strict_types=1);

namespace App\Tests\Unit\Events\Rewards\UseCase;

use App\Domain\Events\Rewards\DTO\PartnerFullInfoRequest;
use App\Domain\Events\Rewards\DTO\RewardIssuanceStateDto;
use App\Domain\Events\Rewards\DTO\SetPartnerStatusRequest;
use App\Domain\Events\Rewards\Entity\Country;
use App\Domain\Events\Rewards\Entity\Event;
use App\Domain\Events\Rewards\Entity\Partner;
use App\Domain\Events\Rewards\Entity\PartnerStatus;
use App\Domain\Events\Rewards\Enum\PartnerStatusType;
use App\Domain\Events\Rewards\Enum\RewardIssuanceStateStatusType;
use App\Domain\Events\Rewards\Repository\EventQueryRepository;
use App\Domain\Events\Rewards\Repository\PartnersFullInfoQueryRepository;
use App\Domain\Events\Rewards\Repository\PartnerStatusCommandRepository;
use App\Domain\Events\Rewards\Repository\PartnerStatusQueryRepository;
use App\Domain\Events\Rewards\Repository\RewardIssuanceStateCommandRepository;
use App\Domain\Events\Rewards\UseCase\SetPartnerStatusUseCase;
use Database\Connection\TransactionInterface;
use DateTimeImmutable;
use DomainException;
use Mockery;

beforeEach(function (): void {
    $this->partnerStatusCommandRepository = Mockery::mock(PartnerStatusCommandRepository::class);
    $this->rewardIssuanceStateCommandRepository = Mockery::mock(RewardIssuanceStateCommandRepository::class);
    $this->partnersQueryRepository = Mockery::mock(PartnersFullInfoQueryRepository::class);
    $this->partnerStatusQueryRepository = Mockery::mock(PartnerStatusQueryRepository::class);
    $this->eventQueryRepository = Mockery::mock(EventQueryRepository::class);
    $this->currentUser = createSecurityUser(1, 'test', 'test@mail.com');
    $this->transaction = Mockery::mock(TransactionInterface::class);

    $this->useCase = new SetPartnerStatusUseCase(
        $this->partnerStatusCommandRepository,
        $this->rewardIssuanceStateCommandRepository,
        $this->partnersQueryRepository,
        $this->partnerStatusQueryRepository,
        $this->eventQueryRepository,
        $this->currentUser,
        $this->transaction,
    );
});

afterEach(function (): void {
    Mockery::close();
});

it('sets partner status to not awarded when actual status is not awarded', function (): void {
    $request = new SetPartnerStatusRequest(1, 1, PartnerStatusType::TO_AWARD, []);
    $partner = new Partner(1, 'Test Partner', 'CONTRACT123', new Country(1, 'Country'), false);
    $event = new Event(1, 'Test Event', new Country(1, 'Country'), 'City', new DateTimeImmutable(), new DateTimeImmutable());

    $this->partnersQueryRepository->shouldReceive('getWithActiveRewards')
        ->with(Mockery::type(PartnerFullInfoRequest::class))
        ->andReturn($partner);

    $this->eventQueryRepository->shouldReceive('getEventByIdFromAllowedList')
        ->with(1)
        ->andReturn($event);

    $this->partnerStatusQueryRepository->shouldReceive('getActualStatusType')
        ->with(1)
        ->andReturn(PartnerStatusType::NOT_AWARDED);

    $this->partnerStatusQueryRepository->shouldReceive('getActualRewardCount')
        ->with(1)
        ->andReturn(0);

    $this->partnerStatusQueryRepository->shouldReceive('getActualPenaltiesCount')
        ->with(1)
        ->andReturn(0);

    $this->partnerStatusQueryRepository->shouldReceive('getPartnerSavedStatus')
        ->with(1)
        ->andReturn(null);

    $this->partnerStatusCommandRepository->shouldReceive('createStatus')
        ->once()
        ->with(Mockery::type(PartnerStatus::class));

    $this->transaction->shouldReceive('beginTransaction')->once();
    $this->transaction->shouldReceive('commit')->once();

    $this->useCase->setPartnerStatus($request);
});

it('creates status when actual status is not verified and no saved status exists', function (): void {
    $request = new SetPartnerStatusRequest(1, 1, PartnerStatusType::TO_AWARD, []);
    $partner = new Partner(1, 'Test Partner', 'CONTRACT123', new Country(1, 'Country'), false);
    $event = new Event(1, 'Test Event', new Country(1, 'Country'), 'City', new DateTimeImmutable(), new DateTimeImmutable());

    $this->partnersQueryRepository->shouldReceive('getWithActiveRewards')
        ->with(Mockery::type(PartnerFullInfoRequest::class))
        ->andReturn($partner);

    $this->eventQueryRepository->shouldReceive('getEventByIdFromAllowedList')
        ->with(1)
        ->andReturn($event);

    $this->partnerStatusQueryRepository->shouldReceive('getActualStatusType')
        ->with(1)
        ->andReturn(PartnerStatusType::NOT_VERIFIED);

    $this->partnerStatusQueryRepository->shouldReceive('getActualRewardCount')
        ->with(1)
        ->andReturn(5);

    $this->partnerStatusQueryRepository->shouldReceive('getActualPenaltiesCount')
        ->with(1)
        ->andReturn(0);

    $this->partnerStatusQueryRepository->shouldReceive('getPartnerSavedStatus')
        ->with(1)
        ->andReturn(null);

    $this->partnerStatusCommandRepository->shouldReceive('createStatus')
        ->once()
        ->with(Mockery::type(PartnerStatus::class));

    $this->transaction->shouldReceive('beginTransaction')->once();
    $this->transaction->shouldReceive('commit')->once();

    $this->useCase->setPartnerStatus($request);
});

it('saves actual status when user did not select any status', function (): void {
    $request = new SetPartnerStatusRequest(1, 1, null, []);
    $partner = new Partner(1, 'Test Partner', 'CONTRACT123', new Country(1, 'Country'), false);
    $event = new Event(1, 'Test Event', new Country(1, 'Country'), 'City', new DateTimeImmutable(), new DateTimeImmutable());

    $this->partnersQueryRepository->shouldReceive('getWithActiveRewards')
        ->with(Mockery::type(PartnerFullInfoRequest::class))
        ->andReturn($partner);

    $this->eventQueryRepository->shouldReceive('getEventByIdFromAllowedList')
        ->with(1)
        ->andReturn($event);

    $this->partnerStatusQueryRepository->shouldReceive('getActualStatusType')
        ->with(1)
        ->andReturn(PartnerStatusType::TO_AWARD);

    $this->partnerStatusQueryRepository->shouldReceive('getActualRewardCount')
        ->with(1)
        ->andReturn(3);

    $this->partnerStatusQueryRepository->shouldReceive('getActualPenaltiesCount')
        ->with(1)
        ->andReturn(1);

    $this->partnerStatusQueryRepository->shouldReceive('getPartnerSavedStatus')
        ->with(1)
        ->andReturn(null);

    $this->partnerStatusCommandRepository->shouldReceive('createStatus')
        ->once()
        ->with(Mockery::type(PartnerStatus::class));

    $this->transaction->shouldReceive('beginTransaction')->once();
    $this->transaction->shouldReceive('commit')->once();

    $this->useCase->setPartnerStatus($request);
});

it('updates counts only when saved status matches requested status', function (): void {
    $request = new SetPartnerStatusRequest(1, 1, PartnerStatusType::TO_AWARD, []);
    $partner = new Partner(1, 'Test Partner', 'CONTRACT123', new Country(1, 'Country'), false);
    $event = new Event(1, 'Test Event', new Country(1, 'Country'), 'City', new DateTimeImmutable(), new DateTimeImmutable());
    $savedStatus = new PartnerStatus(1, 1, PartnerStatusType::TO_AWARD, 3, 1);

    $this->partnersQueryRepository->shouldReceive('getWithActiveRewards')
        ->with(Mockery::type(PartnerFullInfoRequest::class))
        ->andReturn($partner);

    $this->eventQueryRepository->shouldReceive('getEventByIdFromAllowedList')
        ->with(1)
        ->andReturn($event);

    $this->partnerStatusQueryRepository->shouldReceive('getActualStatusType')
        ->with(1)
        ->andReturn(PartnerStatusType::NOT_VERIFIED);

    $this->partnerStatusQueryRepository->shouldReceive('getActualRewardCount')
        ->with(1)
        ->andReturn(5);

    $this->partnerStatusQueryRepository->shouldReceive('getActualPenaltiesCount')
        ->with(1)
        ->andReturn(2);

    $this->partnerStatusQueryRepository->shouldReceive('getPartnerSavedStatus')
        ->with(1)
        ->andReturn($savedStatus);

    $this->partnerStatusCommandRepository->shouldReceive('updateCountsOnly')
        ->once()
        ->with($savedStatus);

    $this->transaction->shouldReceive('beginTransaction')->once();
    $this->transaction->shouldReceive('commit')->once();

    $this->useCase->setPartnerStatus($request);
});

it('updates status when saved status differs from requested status', function (): void {
    $request = new SetPartnerStatusRequest(1, 1, PartnerStatusType::EXCLUDED, []);
    $partner = new Partner(1, 'Test Partner', 'CONTRACT123', new Country(1, 'Country'), false);
    $event = new Event(1, 'Test Event', new Country(1, 'Country'), 'City', new DateTimeImmutable(), new DateTimeImmutable());
    $savedStatus = new PartnerStatus(1, 1, PartnerStatusType::TO_AWARD, 3, 1);

    $this->partnersQueryRepository->shouldReceive('getWithActiveRewards')
        ->with(Mockery::type(PartnerFullInfoRequest::class))
        ->andReturn($partner);

    $this->eventQueryRepository->shouldReceive('getEventByIdFromAllowedList')
        ->with(1)
        ->andReturn($event);

    $this->partnerStatusQueryRepository->shouldReceive('getActualStatusType')
        ->with(1)
        ->andReturn(PartnerStatusType::NOT_VERIFIED);

    $this->partnerStatusQueryRepository->shouldReceive('getActualRewardCount')
        ->with(1)
        ->andReturn(5);

    $this->partnerStatusQueryRepository->shouldReceive('getActualPenaltiesCount')
        ->with(1)
        ->andReturn(2);

    $this->partnerStatusQueryRepository->shouldReceive('getPartnerSavedStatus')
        ->with(1)
        ->andReturn($savedStatus);

    $this->partnerStatusCommandRepository->shouldReceive('updateStatus')
        ->once()
        ->with($savedStatus);

    $this->transaction->shouldReceive('beginTransaction')->once();
    $this->transaction->shouldReceive('commit')->once();

    $this->useCase->setPartnerStatus($request);
});

it('throws exception when trying to manually set not awarded status', function (): void {
    $request = new SetPartnerStatusRequest(1, 1, PartnerStatusType::NOT_AWARDED, []);
    $partner = new Partner(1, 'Test Partner', 'CONTRACT123', new Country(1, 'Country'), false);

    $this->partnersQueryRepository->shouldReceive('getWithActiveRewards')
        ->with(Mockery::type(PartnerFullInfoRequest::class))
        ->andReturn($partner);

    $this->expectException(DomainException::class);
    $this->expectExceptionMessage('Нельзя вручную назначить статус "Не награждается"');

    $this->useCase->setPartnerStatus($request);
});

it('throws exception when trying to change status from excluded back to not verified', function (): void {
    $request = new SetPartnerStatusRequest(1, 1, PartnerStatusType::NOT_VERIFIED, []);
    $savedStatus = new PartnerStatus(1, 1, PartnerStatusType::EXCLUDED, 3, 1);
    $partner = new Partner(1, 'Test Partner', 'CONTRACT123', new Country(1, 'Country'), false, status: $savedStatus);

    $this->partnersQueryRepository->shouldReceive('getWithActiveRewards')
        ->with(Mockery::type(PartnerFullInfoRequest::class))
        ->andReturn($partner);

    $this->expectException(DomainException::class);
    $this->expectExceptionMessage('Если статус контракта изменен с "Не проверен" на "К выдаче" или "Исключен", то вручную вернуть статус на "Не проверен" невозможно');

    $this->useCase->setPartnerStatus($request);
});

it('throws exception when trying to change status of partner with not awarded status', function (): void {
    $request = new SetPartnerStatusRequest(1, 1, PartnerStatusType::TO_AWARD, []);
    $savedStatus = new PartnerStatus(1, 1, PartnerStatusType::NOT_AWARDED, 0, 1);
    $partner = new Partner(1, 'Test Partner', 'CONTRACT123', new Country(1, 'Country'), false, status: $savedStatus);

    $this->partnersQueryRepository->shouldReceive('getWithActiveRewards')
        ->with(Mockery::type(PartnerFullInfoRequest::class))
        ->andReturn($partner);

    $this->expectException(DomainException::class);
    $this->expectExceptionMessage("нельзя вручную менять статус у партнёров со статусом 'Не награждается'");

    $this->useCase->setPartnerStatus($request);
});

it('does not process reward states when partner status is excluded', function (): void {
    $rewardStates = [
        new RewardIssuanceStateDto(1, RewardIssuanceStateStatusType::REWARDED_FULL, 'comment'),
    ];
    $request = new SetPartnerStatusRequest(1, 1, PartnerStatusType::EXCLUDED, $rewardStates);
    $partner = new Partner(1, 'Test Partner', 'CONTRACT123', new Country(1, 'Country'), false);
    $event = new Event(1, 'Test Event', new Country(1, 'Country'), 'City', new DateTimeImmutable(), new DateTimeImmutable());

    $this->partnersQueryRepository->shouldReceive('getWithActiveRewards')
        ->with(Mockery::type(PartnerFullInfoRequest::class))
        ->andReturn($partner);

    $this->eventQueryRepository->shouldReceive('getEventByIdFromAllowedList')
        ->with(1)
        ->andReturn($event);

    $this->partnerStatusQueryRepository->shouldReceive('getActualStatusType')
        ->with(1)
        ->andReturn(PartnerStatusType::NOT_VERIFIED);

    $this->partnerStatusQueryRepository->shouldReceive('getActualRewardCount')
        ->with(1)
        ->andReturn(5);

    $this->partnerStatusQueryRepository->shouldReceive('getActualPenaltiesCount')
        ->with(1)
        ->andReturn(0);

    $this->partnerStatusQueryRepository->shouldReceive('getPartnerSavedStatus')
        ->with(1)
        ->andReturn(null);

    $this->partnerStatusCommandRepository->shouldReceive('createStatus')
        ->once()
        ->with(Mockery::type(PartnerStatus::class));

    // Не должно быть вызовов к rewardIssuanceStateCommandRepository
    $this->rewardIssuanceStateCommandRepository->shouldNotReceive('setStatus');
    $this->rewardIssuanceStateCommandRepository->shouldNotReceive('setComment');

    $this->transaction->shouldReceive('beginTransaction')->once();
    $this->transaction->shouldReceive('commit')->once();

    $this->useCase->setPartnerStatus($request);
});
