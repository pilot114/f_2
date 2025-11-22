<?php

declare(strict_types=1);

namespace App\Tests\Unit\Events\Rewards\UseCase;

use App\Domain\Events\Rewards\DTO\PartnerFullInfoRequest;
use App\Domain\Events\Rewards\DTO\RewardIssuanceStateDto;
use App\Domain\Events\Rewards\DTO\SetRewardIssuanceStateStatusRequest;
use App\Domain\Events\Rewards\Entity\Country;
use App\Domain\Events\Rewards\Entity\Nomination;
use App\Domain\Events\Rewards\Entity\Partner;
use App\Domain\Events\Rewards\Entity\PartnerStatus;
use App\Domain\Events\Rewards\Entity\Program;
use App\Domain\Events\Rewards\Entity\Reward;
use App\Domain\Events\Rewards\Entity\RewardIssuanceState;
use App\Domain\Events\Rewards\Enum\PartnerStatusType;
use App\Domain\Events\Rewards\Enum\RewardIssuanceStateStatusType;
use App\Domain\Events\Rewards\Repository\PartnersFullInfoQueryRepository;
use App\Domain\Events\Rewards\Repository\PartnerStatusCommandRepository;
use App\Domain\Events\Rewards\Repository\PartnerStatusQueryRepository;
use App\Domain\Events\Rewards\Repository\RewardIssuanceStateCommandRepository;
use App\Domain\Events\Rewards\UseCase\SetRewardIssuanceStateStatusUseCase;
use Database\Connection\TransactionInterface;
use DateTimeImmutable;
use DomainException;
use Mockery;

beforeEach(function (): void {
    $this->rewardIssuanceStateCommandRepository = Mockery::mock(RewardIssuanceStateCommandRepository::class);
    $this->partnersQueryRepository = Mockery::mock(PartnersFullInfoQueryRepository::class);
    $this->partnerStatusQueryRepository = Mockery::mock(PartnerStatusQueryRepository::class);
    $this->partnerStatusCommandRepository = Mockery::mock(PartnerStatusCommandRepository::class);
    $this->transaction = Mockery::mock(TransactionInterface::class);
    $this->currentUser = createSecurityUser(1, 'test', 'test@mail.com');

    $this->useCase = new SetRewardIssuanceStateStatusUseCase(
        $this->rewardIssuanceStateCommandRepository,
        $this->partnersQueryRepository,
        $this->partnerStatusQueryRepository,
        $this->partnerStatusCommandRepository,
        $this->transaction,
        $this->currentUser
    );
});

afterEach(function (): void {
    Mockery::close();
});

it('successfully updates reward issuance state status', function (): void {
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

    $rewardStateDto = new RewardIssuanceStateDto(1, RewardIssuanceStateStatusType::REWARDED_FULL, 'Награда выдана');
    $request = new SetRewardIssuanceStateStatusRequest(1, [$rewardStateDto]);

    $this->partnersQueryRepository->shouldReceive('getWithActiveRewards')
        ->with(Mockery::type(PartnerFullInfoRequest::class))
        ->andReturn($partner);

    $this->rewardIssuanceStateCommandRepository->shouldReceive('setStatus')
        ->once()
        ->with($rewardIssuanceState);

    $this->partnerStatusQueryRepository->shouldReceive('getActualPenaltiesCount')
        ->with(1)
        ->andReturn(0);

    $this->partnerStatusQueryRepository->shouldReceive('getActualRewardCount')
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

    $this->useCase->setRewardIssuanceStateStatus($request);
});

it('updates only comment when status matches request', function (): void {
    $program = new Program(1, 'Test Program');
    $nomination = new Nomination(1, 'Test Nomination', $program);
    $reward = new Reward(1, 'Test Reward', 100, $nomination);

    $rewardIssuanceState = new RewardIssuanceState(
        id: 1,
        calculationResultId: 100,
        program: $program,
        nomination: $nomination,
        rewardsCount: 1,
        status: RewardIssuanceStateStatusType::NOT_REWARDED, // Изменили статус, чтобы не было REWARDED_FULL
        winDate: new DateTimeImmutable(),
        reward: $reward,
        note: 'Старый комментарий'
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

    $rewardStateDto = new RewardIssuanceStateDto(1, RewardIssuanceStateStatusType::NOT_REWARDED, 'Новый комментарий');
    $request = new SetRewardIssuanceStateStatusRequest(1, [$rewardStateDto]);

    $this->partnersQueryRepository->shouldReceive('getWithActiveRewards')
        ->with(Mockery::type(PartnerFullInfoRequest::class))
        ->andReturn($partner);

    $this->rewardIssuanceStateCommandRepository->shouldReceive('setComment')
        ->once()
        ->with($rewardIssuanceState);

    $this->partnerStatusQueryRepository->shouldReceive('getActualPenaltiesCount')
        ->with(1)
        ->andReturn(0);

    $this->partnerStatusQueryRepository->shouldReceive('getActualRewardCount')
        ->with(1)
        ->andReturn(5);

    $this->partnerStatusQueryRepository->shouldReceive('getPartnerSavedStatus')
        ->with(1)
        ->andReturn(null);

    $this->partnerStatusCommandRepository->shouldReceive('createStatus')
        ->once()
        ->with(Mockery::type(PartnerStatus::class));

    $this->transaction->shouldReceive('beginTransaction')->once();
    $this->transaction->shouldReceive('commit')->once();

    $this->useCase->setRewardIssuanceStateStatus($request);
});

it('sets partner status to not awarded when no rewards left', function (): void {
    $partner = new Partner(
        id: 1,
        name: 'Test Partner',
        contract: 'CONTRACT123',
        country: new Country(1, 'Country'),
        isFamily: false
    );

    $request = new SetRewardIssuanceStateStatusRequest(1, []);

    $this->partnersQueryRepository->shouldReceive('getWithActiveRewards')
        ->with(Mockery::type(PartnerFullInfoRequest::class))
        ->andReturn($partner);

    $this->partnerStatusQueryRepository->shouldReceive('getActualPenaltiesCount')
        ->with(1)
        ->andReturn(1);

    $this->partnerStatusQueryRepository->shouldReceive('getActualRewardCount')
        ->with(1)
        ->andReturn(0);

    $this->partnerStatusQueryRepository->shouldReceive('getPartnerSavedStatus')
        ->with(1)
        ->andReturn(null);

    $this->partnerStatusCommandRepository->shouldReceive('createStatus')
        ->once()
        ->with(Mockery::on(function ($status): bool {
            return $status->getStatusType() === PartnerStatusType::NOT_AWARDED;
        }));

    $this->transaction->shouldReceive('beginTransaction')->once();
    $this->transaction->shouldReceive('commit')->once();

    $this->useCase->setRewardIssuanceStateStatus($request);
});

it('sets partner status to to award when rewards are available', function (): void {
    $partner = new Partner(
        id: 1,
        name: 'Test Partner',
        contract: 'CONTRACT123',
        country: new Country(1, 'Country'),
        isFamily: false
    );

    $request = new SetRewardIssuanceStateStatusRequest(1, []);

    $this->partnersQueryRepository->shouldReceive('getWithActiveRewards')
        ->with(Mockery::type(PartnerFullInfoRequest::class))
        ->andReturn($partner);

    $this->partnerStatusQueryRepository->shouldReceive('getActualPenaltiesCount')
        ->with(1)
        ->andReturn(0);

    $this->partnerStatusQueryRepository->shouldReceive('getActualRewardCount')
        ->with(1)
        ->andReturn(3);

    $this->partnerStatusQueryRepository->shouldReceive('getPartnerSavedStatus')
        ->with(1)
        ->andReturn(null);

    $this->partnerStatusCommandRepository->shouldReceive('createStatus')
        ->once()
        ->with(Mockery::on(function ($status): bool {
            return $status->getStatusType() === PartnerStatusType::TO_AWARD;
        }));

    $this->transaction->shouldReceive('beginTransaction')->once();
    $this->transaction->shouldReceive('commit')->once();

    $this->useCase->setRewardIssuanceStateStatus($request);
});

it('updates existing partner status', function (): void {
    $existingStatus = new PartnerStatus(1, 1, PartnerStatusType::TO_AWARD, 2, 0);
    $partner = new Partner(
        id: 1,
        name: 'Test Partner',
        contract: 'CONTRACT123',
        country: new Country(1, 'Country'),
        isFamily: false
    );

    $request = new SetRewardIssuanceStateStatusRequest(1, []);

    $this->partnersQueryRepository->shouldReceive('getWithActiveRewards')
        ->with(Mockery::type(PartnerFullInfoRequest::class))
        ->andReturn($partner);

    $this->partnerStatusQueryRepository->shouldReceive('getActualPenaltiesCount')
        ->with(1)
        ->andReturn(1);

    $this->partnerStatusQueryRepository->shouldReceive('getActualRewardCount')
        ->with(1)
        ->andReturn(5);

    $this->partnerStatusQueryRepository->shouldReceive('getPartnerSavedStatus')
        ->with(1)
        ->andReturn($existingStatus);

    $this->partnerStatusCommandRepository->shouldReceive('updateStatus')
        ->once()
        ->with($existingStatus);

    $this->transaction->shouldReceive('beginTransaction')->once();
    $this->transaction->shouldReceive('commit')->once();

    $this->useCase->setRewardIssuanceStateStatus($request);
});

it('throws exception when reward issuance state not found', function (): void {
    $partner = new Partner(
        id: 1,
        name: 'Test Partner',
        contract: 'CONTRACT123',
        country: new Country(1, 'Country'),
        isFamily: false,
        rewardIssuanceStates: []
    );

    $rewardStateDto = new RewardIssuanceStateDto(999, RewardIssuanceStateStatusType::REWARDED_FULL, null);
    $request = new SetRewardIssuanceStateStatusRequest(1, [$rewardStateDto]);

    $this->partnersQueryRepository->shouldReceive('getWithActiveRewards')
        ->with(Mockery::type(PartnerFullInfoRequest::class))
        ->andReturn($partner);

    $this->transaction->shouldReceive('beginTransaction')->once();

    $this->expectException(DomainException::class);
    $this->expectExceptionMessage('У партера нет состояния выдачи награды с id 999');

    $this->useCase->setRewardIssuanceStateStatus($request);
});

it('skips processing when reward state is already fully rewarded', function (): void {
    $program = new Program(1, 'Test Program');
    $nomination = new Nomination(1, 'Test Nomination', $program);
    $reward = new Reward(1, 'Test Reward', 100, $nomination);

    $rewardIssuanceState = new RewardIssuanceState(
        id: 1,
        calculationResultId: 100,
        program: $program,
        nomination: $nomination,
        rewardsCount: 1,
        status: RewardIssuanceStateStatusType::REWARDED_FULL,
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

    $rewardStateDto = new RewardIssuanceStateDto(1, RewardIssuanceStateStatusType::NOT_REWARDED, 'Попытка изменить');
    $request = new SetRewardIssuanceStateStatusRequest(1, [$rewardStateDto]);

    $this->partnersQueryRepository->shouldReceive('getWithActiveRewards')
        ->with(Mockery::type(PartnerFullInfoRequest::class))
        ->andReturn($partner);

    // Не должно быть вызовов к rewardIssuanceStateCommandRepository
    $this->rewardIssuanceStateCommandRepository->shouldNotReceive('setStatus');
    $this->rewardIssuanceStateCommandRepository->shouldNotReceive('setComment');

    $this->partnerStatusQueryRepository->shouldReceive('getActualPenaltiesCount')
        ->with(1)
        ->andReturn(0);

    $this->partnerStatusQueryRepository->shouldReceive('getActualRewardCount')
        ->with(1)
        ->andReturn(5);

    $this->partnerStatusQueryRepository->shouldReceive('getPartnerSavedStatus')
        ->with(1)
        ->andReturn(null);

    $this->partnerStatusCommandRepository->shouldReceive('createStatus')
        ->once()
        ->with(Mockery::type(PartnerStatus::class));

    $this->transaction->shouldReceive('beginTransaction')->once();
    $this->transaction->shouldReceive('commit')->once();

    $this->useCase->setRewardIssuanceStateStatus($request);
});
