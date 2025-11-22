<?php

declare(strict_types=1);

namespace App\Tests\Unit\Events\Rewards\Entity;

use App\Domain\Events\Rewards\Entity\Country;
use App\Domain\Events\Rewards\Entity\Nomination;
use App\Domain\Events\Rewards\Entity\Partner;
use App\Domain\Events\Rewards\Entity\PartnerStatus;
use App\Domain\Events\Rewards\Entity\Penalty;
use App\Domain\Events\Rewards\Entity\Program;
use App\Domain\Events\Rewards\Entity\Rang;
use App\Domain\Events\Rewards\Entity\Registration;
use App\Domain\Events\Rewards\Entity\Reward;
use App\Domain\Events\Rewards\Entity\RewardIssuanceState;
use App\Domain\Events\Rewards\Enum\PartnerStatusType;
use App\Domain\Events\Rewards\Enum\RewardIssuanceStateStatusType;
use DateTimeImmutable;

it('returns empty array when no reward issuance states', function (): void {
    $country = new Country(1, 'Test Country');
    $partner = new Partner(
        id: 1,
        name: 'Test Partner',
        contract: 'CONTRACT123',
        country: $country,
        isFamily: false
    );

    $nominationIds = $partner->getNominationIds();

    expect($nominationIds)->toBe([]);
});

it('returns single nomination id', function (): void {
    $country = new Country(1, 'Test Country');
    $program = new Program(1, 'Test Program');
    $nomination = new Nomination(10, 'Test Nomination', $program);
    $reward = new Reward(1, 'Test Reward', 1, $nomination);

    $rewardIssuanceState = new RewardIssuanceState(
        id: 1,
        calculationResultId: 1,
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
        country: $country,
        isFamily: false,
        rewardIssuanceStates: [$rewardIssuanceState]
    );

    $nominationIds = $partner->getNominationIds();

    expect($nominationIds)->toBe([10]);
});

it('returns multiple unique nomination ids', function (): void {
    $country = new Country(1, 'Test Country');
    $program1 = new Program(1, 'Test Program 1');
    $program2 = new Program(2, 'Test Program 2');

    $nomination1 = new Nomination(10, 'Test Nomination 1', $program1);
    $nomination2 = new Nomination(20, 'Test Nomination 2', $program2);
    $nomination3 = new Nomination(30, 'Test Nomination 3', $program1);

    $reward1 = new Reward(1, 'Test Reward 1', 1, $nomination1);
    $reward2 = new Reward(2, 'Test Reward 2', 2, $nomination2);
    $reward3 = new Reward(3, 'Test Reward 3', 3, $nomination3);

    $rewardIssuanceState1 = new RewardIssuanceState(
        id: 1,
        calculationResultId: 1,
        program: $program1,
        nomination: $nomination1,
        rewardsCount: 1,
        status: RewardIssuanceStateStatusType::NOT_REWARDED,
        winDate: new DateTimeImmutable(),
        reward: $reward1
    );

    $rewardIssuanceState2 = new RewardIssuanceState(
        id: 2,
        calculationResultId: 2,
        program: $program2,
        nomination: $nomination2,
        rewardsCount: 1,
        status: RewardIssuanceStateStatusType::REWARDED_FULL,
        winDate: new DateTimeImmutable(),
        reward: $reward2
    );

    $rewardIssuanceState3 = new RewardIssuanceState(
        id: 3,
        calculationResultId: 3,
        program: $program1,
        nomination: $nomination3,
        rewardsCount: 1,
        status: RewardIssuanceStateStatusType::REWARDED_FULL,
        winDate: new DateTimeImmutable(),
        reward: $reward3
    );

    $partner = new Partner(
        id: 1,
        name: 'Test Partner',
        contract: 'CONTRACT123',
        country: $country,
        isFamily: false,
        rewardIssuanceStates: [$rewardIssuanceState1, $rewardIssuanceState2, $rewardIssuanceState3]
    );

    $nominationIds = $partner->getNominationIds();

    expect($nominationIds)->toBe([10, 20, 30]);
});

it('returns unique nomination ids when duplicates exist', function (): void {
    $country = new Country(1, 'Test Country');
    $program = new Program(1, 'Test Program');
    $nomination = new Nomination(10, 'Test Nomination', $program);

    $reward1 = new Reward(1, 'Test Reward 1', 1, $nomination);
    $reward2 = new Reward(2, 'Test Reward 2', 2, $nomination);
    $reward3 = new Reward(3, 'Test Reward 3', 3, $nomination);

    $rewardIssuanceState1 = new RewardIssuanceState(
        id: 1,
        calculationResultId: 1,
        program: $program,
        nomination: $nomination,
        rewardsCount: 1,
        status: RewardIssuanceStateStatusType::NOT_REWARDED,
        winDate: new DateTimeImmutable(),
        reward: $reward1
    );

    $rewardIssuanceState2 = new RewardIssuanceState(
        id: 2,
        calculationResultId: 2,
        program: $program,
        nomination: $nomination,
        rewardsCount: 2,
        status: RewardIssuanceStateStatusType::REWARDED_FULL,
        winDate: new DateTimeImmutable(),
        reward: $reward2
    );

    $rewardIssuanceState3 = new RewardIssuanceState(
        id: 3,
        calculationResultId: 3,
        program: $program,
        nomination: $nomination,
        rewardsCount: 1,
        status: RewardIssuanceStateStatusType::REWARDED_FULL,
        winDate: new DateTimeImmutable(),
        reward: $reward3
    );

    $partner = new Partner(
        id: 1,
        name: 'Test Partner',
        contract: 'CONTRACT123',
        country: $country,
        isFamily: false,
        rewardIssuanceStates: [$rewardIssuanceState1, $rewardIssuanceState2, $rewardIssuanceState3]
    );

    $nominationIds = $partner->getNominationIds();

    expect($nominationIds)->toBe([10]);
    expect(count($nominationIds))->toBe(1);
});

it('returns array values when nomination ids have gaps', function (): void {
    $country = new Country(1, 'Test Country');
    $program = new Program(1, 'Test Program');

    $nomination1 = new Nomination(5, 'Test Nomination 1', $program);
    $nomination2 = new Nomination(15, 'Test Nomination 2', $program);
    $nomination3 = new Nomination(25, 'Test Nomination 3', $program);

    $reward1 = new Reward(1, 'Test Reward 1', 1, $nomination1);
    $reward2 = new Reward(2, 'Test Reward 2', 2, $nomination2);
    $reward3 = new Reward(3, 'Test Reward 3', 3, $nomination3);

    $rewardIssuanceState1 = new RewardIssuanceState(
        id: 1,
        calculationResultId: 1,
        program: $program,
        nomination: $nomination1,
        rewardsCount: 1,
        status: RewardIssuanceStateStatusType::NOT_REWARDED,
        winDate: new DateTimeImmutable(),
        reward: $reward1
    );

    $rewardIssuanceState2 = new RewardIssuanceState(
        id: 2,
        calculationResultId: 2,
        program: $program,
        nomination: $nomination2,
        rewardsCount: 1,
        status: RewardIssuanceStateStatusType::REWARDED_FULL,
        winDate: new DateTimeImmutable(),
        reward: $reward2
    );

    $rewardIssuanceState3 = new RewardIssuanceState(
        id: 3,
        calculationResultId: 3,
        program: $program,
        nomination: $nomination3,
        rewardsCount: 1,
        status: RewardIssuanceStateStatusType::REWARDED_FULL,
        winDate: new DateTimeImmutable(),
        reward: $reward3
    );

    $partner = new Partner(
        id: 1,
        name: 'Test Partner',
        contract: 'CONTRACT123',
        country: $country,
        isFamily: false,
        rewardIssuanceStates: [$rewardIssuanceState1, $rewardIssuanceState2, $rewardIssuanceState3]
    );

    $nominationIds = $partner->getNominationIds();

    expect($nominationIds)->toBe([5, 15, 25]);
    expect(array_keys($nominationIds))->toBe([0, 1, 2]); // Проверяем что массив переиндексирован
});

it('gets calculation result ids', function (): void {
    $country = new Country(1, 'Test Country');
    $program = new Program(1, 'Test Program');
    $nomination = new Nomination(1, 'Test Nomination', $program);
    $reward = new Reward(1, 'Test Reward', 1, $nomination);

    $rewardIssuanceState1 = new RewardIssuanceState(
        id: 1,
        calculationResultId: 100,
        program: $program,
        nomination: $nomination,
        rewardsCount: 1,
        status: RewardIssuanceStateStatusType::NOT_REWARDED,
        winDate: new DateTimeImmutable(),
        reward: $reward
    );

    $rewardIssuanceState2 = new RewardIssuanceState(
        id: 2,
        calculationResultId: 200,
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
        country: $country,
        isFamily: false,
        rewardIssuanceStates: [$rewardIssuanceState1, $rewardIssuanceState2]
    );

    $calculationResultIds = $partner->getCalculationResultIds();

    expect($calculationResultIds)->toBe([100, 200]);
});

it('gets calculation result ids with duplicates', function (): void {
    $country = new Country(1, 'Test Country');
    $program = new Program(1, 'Test Program');
    $nomination = new Nomination(1, 'Test Nomination', $program);
    $reward = new Reward(1, 'Test Reward', 1, $nomination);

    $rewardIssuanceState1 = new RewardIssuanceState(
        id: 1,
        calculationResultId: 100,
        program: $program,
        nomination: $nomination,
        rewardsCount: 1,
        status: RewardIssuanceStateStatusType::NOT_REWARDED,
        winDate: new DateTimeImmutable(),
        reward: $reward
    );

    $rewardIssuanceState2 = new RewardIssuanceState(
        id: 2,
        calculationResultId: 100, // Дубликат
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
        country: $country,
        isFamily: false,
        rewardIssuanceStates: [$rewardIssuanceState1, $rewardIssuanceState2]
    );

    $calculationResultIds = $partner->getCalculationResultIds();

    expect($calculationResultIds)->toBe([100]);
    expect(count($calculationResultIds))->toBe(1);
});

it('gets filtered rewards count', function (): void {
    $country = new Country(1, 'Test Country');
    $program = new Program(1, 'Test Program');
    $nomination = new Nomination(1, 'Test Nomination', $program);
    $reward = new Reward(1, 'Test Reward', 1, $nomination);

    $rewardIssuanceState1 = new RewardIssuanceState(
        id: 1,
        calculationResultId: 100,
        program: $program,
        nomination: $nomination,
        rewardsCount: 1,
        status: RewardIssuanceStateStatusType::NOT_REWARDED,
        winDate: new DateTimeImmutable(),
        reward: $reward
    );

    $rewardIssuanceState2 = new RewardIssuanceState(
        id: 2,
        calculationResultId: 200,
        program: $program,
        nomination: $nomination,
        rewardsCount: 2,
        status: RewardIssuanceStateStatusType::REWARDED_FULL,
        winDate: new DateTimeImmutable(),
        reward: $reward
    );

    $partner = new Partner(
        id: 1,
        name: 'Test Partner',
        contract: 'CONTRACT123',
        country: $country,
        isFamily: false,
        rewardIssuanceStates: [$rewardIssuanceState1, $rewardIssuanceState2]
    );

    expect($partner->getFilteredRewardsCount())->toBe(2);
});

it('gets reward issuance states', function (): void {
    $country = new Country(1, 'Test Country');
    $program = new Program(1, 'Test Program');
    $nomination = new Nomination(1, 'Test Nomination', $program);
    $reward = new Reward(1, 'Test Reward', 1, $nomination);

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
        country: $country,
        isFamily: false,
        rewardIssuanceStates: [$rewardIssuanceState]
    );

    $states = $partner->getRewardIssuanceStates();

    expect($states)->toHaveCount(1);
    expect($states[0])->toBe($rewardIssuanceState);
});

it('converts to partner common response', function (): void {
    $country = new Country(1, 'Россия');

    $partner = new Partner(
        id: 1,
        name: 'Иван Иванов',
        contract: 'CONTRACT123',
        country: $country,
        isFamily: true
    );

    $result = $partner->toPartnerCommonResponse();

    expect($result->id)->toBe(1);
    expect($result->name)->toBe('Иван Иванов');
    expect($result->contract)->toBe('CONTRACT123');
    expect($result->country->id)->toBe($country->toCountryResponse()->id);
    expect($result->country->name)->toBe($country->toCountryResponse()->name);
    expect($result->isFamily)->toBeTrue();
});

it('gets rang when set', function (): void {
    $country = new Country(1, 'Test Country');
    $rang = new Rang(
        id: 1,
        rang: 'BR1',
        name: 'Бронзовый',
        date: new DateTimeImmutable('2024-01-01')
    );

    $partner = new Partner(
        id: 1,
        name: 'Test Partner',
        contract: 'CONTRACT123',
        country: $country,
        isFamily: false,
        rang: $rang
    );

    expect($partner->getRang())->toBe($rang);
});

it('gets rang when null', function (): void {
    $country = new Country(1, 'Test Country');

    $partner = new Partner(
        id: 1,
        name: 'Test Partner',
        contract: 'CONTRACT123',
        country: $country,
        isFamily: false
    );

    expect($partner->getRang())->toBeNull();
});

it('gets penalties', function (): void {
    $country = new Country(1, 'Test Country');
    $penalty = new Penalty(
        id: 1,
        name: 'Штраф',
        prim: 'Описание',
        start: new DateTimeImmutable('2024-01-01')
    );

    $partner = new Partner(
        id: 1,
        name: 'Test Partner',
        contract: 'CONTRACT123',
        country: $country,
        isFamily: false,
        penalties: [$penalty]
    );

    $penalties = $partner->getPenalties();

    expect($penalties)->toHaveCount(1);
    expect($penalties[0])->toBe($penalty);
});

it('gets status when set', function (): void {
    $country = new Country(1, 'Test Country');
    $status = new PartnerStatus(
        id: 1,
        partnerId: 1,
        statusType: PartnerStatusType::TO_AWARD,
        rewardsCount: 5,
        penaltiesCount: 2
    );

    $partner = new Partner(
        id: 1,
        name: 'Test Partner',
        contract: 'CONTRACT123',
        country: $country,
        isFamily: false,
        status: $status
    );

    expect($partner->getStatus())->toBe($status);
});

it('gets status when null', function (): void {
    $country = new Country(1, 'Test Country');

    $partner = new Partner(
        id: 1,
        name: 'Test Partner',
        contract: 'CONTRACT123',
        country: $country,
        isFamily: false
    );

    expect($partner->getStatus())->toBeNull();
});

it('creates partner status from actual data in constructor', function (): void {
    $country = new Country(1, 'Test Country');

    $partner = new Partner(
        id: 1,
        name: 'Test Partner',
        contract: 'CONTRACT123',
        country: $country,
        isFamily: false,
        actualStatusType: PartnerStatusType::TO_AWARD,
        actualRewardsCount: 5,
        actualPenaltiesCount: 2
    );

    $status = $partner->getStatus();

    expect($status)->not->toBeNull();
    expect($status->getStatusType())->toBe(PartnerStatusType::TO_AWARD);
    expect($status->getRewardsCount())->toBe(5);
    expect($status->getPenaltiesCount())->toBe(2);
});

it('updates existing status with actual data in constructor', function (): void {
    $country = new Country(1, 'Test Country');
    $existingStatus = new PartnerStatus(
        id: 1,
        partnerId: 1,
        statusType: PartnerStatusType::NOT_VERIFIED,
        rewardsCount: 0,
        penaltiesCount: 0
    );

    $partner = new Partner(
        id: 1,
        name: 'Test Partner',
        contract: 'CONTRACT123',
        country: $country,
        isFamily: false,
        status: $existingStatus,
        actualStatusType: PartnerStatusType::TO_AWARD,
        actualRewardsCount: 5,
        actualPenaltiesCount: 2
    );

    $status = $partner->getStatus();

    expect($status)->toBe($existingStatus);
    expect($status->getStatusType())->toBe(PartnerStatusType::TO_AWARD);
    expect($status->getRewardsCount())->toBe(5);
    expect($status->getPenaltiesCount())->toBe(2);
});

it('does not create status when actual data is incomplete', function (): void {
    $country = new Country(1, 'Test Country');

    // Только statusType, но без counts
    $partner1 = new Partner(
        id: 1,
        name: 'Test Partner',
        contract: 'CONTRACT123',
        country: $country,
        isFamily: false,
        actualStatusType: PartnerStatusType::TO_AWARD
    );

    expect($partner1->getStatus())->toBeNull();

    // Только counts, но без statusType
    $partner2 = new Partner(
        id: 2,
        name: 'Test Partner 2',
        contract: 'CONTRACT456',
        country: $country,
        isFamily: false,
        actualRewardsCount: 5,
        actualPenaltiesCount: 2
    );

    expect($partner2->getStatus())->toBeNull();
});

it('adds deleted rewards to existing reward issuance states', function (): void {
    $country = new Country(1, 'Test Country');
    $program = new Program(1, 'Test Program');
    $nomination = new Nomination(1, 'Test Nomination', $program);
    $reward = new Reward(1, 'Test Reward', 1, $nomination);

    $existingRewardIssuanceState = new RewardIssuanceState(
        id: 1,
        calculationResultId: 100,
        program: $program,
        nomination: $nomination,
        rewardsCount: 1,
        status: RewardIssuanceStateStatusType::NOT_REWARDED,
        winDate: new DateTimeImmutable(),
        reward: $reward
    );

    $deletedRewardIssuanceState = new RewardIssuanceState(
        id: 2,
        calculationResultId: 200,
        program: $program,
        nomination: $nomination,
        rewardsCount: 2,
        status: RewardIssuanceStateStatusType::REWARDED_FULL,
        winDate: new DateTimeImmutable(),
        reward: $reward
    );

    $partner = new Partner(
        id: 1,
        name: 'Test Partner',
        contract: 'CONTRACT123',
        country: $country,
        isFamily: false,
        rewardIssuanceStates: [$existingRewardIssuanceState]
    );

    $partner->addDeletedRewards([$deletedRewardIssuanceState]);

    $states = $partner->getRewardIssuanceStates();
    expect($states)->toHaveCount(2);
    expect($states[0])->toBe($existingRewardIssuanceState);
    expect($states[1])->toBe($deletedRewardIssuanceState);
});

it('adds multiple deleted rewards', function (): void {
    $country = new Country(1, 'Test Country');
    $program = new Program(1, 'Test Program');
    $nomination = new Nomination(1, 'Test Nomination', $program);
    $reward = new Reward(1, 'Test Reward', 1, $nomination);

    $deletedReward1 = new RewardIssuanceState(
        id: 1,
        calculationResultId: 100,
        program: $program,
        nomination: $nomination,
        rewardsCount: 1,
        status: RewardIssuanceStateStatusType::NOT_REWARDED,
        winDate: new DateTimeImmutable(),
        reward: $reward
    );

    $deletedReward2 = new RewardIssuanceState(
        id: 2,
        calculationResultId: 200,
        program: $program,
        nomination: $nomination,
        rewardsCount: 2,
        status: RewardIssuanceStateStatusType::REWARDED_FULL,
        winDate: new DateTimeImmutable(),
        reward: $reward
    );

    $partner = new Partner(
        id: 1,
        name: 'Test Partner',
        contract: 'CONTRACT123',
        country: $country,
        isFamily: false
    );

    $partner->addDeletedRewards([$deletedReward1, $deletedReward2]);

    $states = $partner->getRewardIssuanceStates();
    expect($states)->toHaveCount(2);
    expect($states[0])->toBe($deletedReward1);
    expect($states[1])->toBe($deletedReward2);
});

it('gets rewards grouped by nomination', function (): void {
    $country = new Country(1, 'Test Country');
    $program1 = new Program(1, 'Test Program 1');
    $program2 = new Program(2, 'Test Program 2');

    $nomination1 = new Nomination(10, 'Test Nomination 1', $program1);
    $nomination2 = new Nomination(20, 'Test Nomination 2', $program2);

    $reward1 = new Reward(1, 'Test Reward 1', 1, $nomination1);
    $reward2 = new Reward(2, 'Test Reward 2', 2, $nomination2);
    $reward3 = new Reward(3, 'Test Reward 3', 3, $nomination1);

    $rewardIssuanceState1 = new RewardIssuanceState(
        id: 1,
        calculationResultId: 100,
        program: $program1,
        nomination: $nomination1,
        rewardsCount: 1,
        status: RewardIssuanceStateStatusType::NOT_REWARDED,
        winDate: new DateTimeImmutable(),
        reward: $reward1
    );

    $rewardIssuanceState2 = new RewardIssuanceState(
        id: 2,
        calculationResultId: 200,
        program: $program2,
        nomination: $nomination2,
        rewardsCount: 2,
        status: RewardIssuanceStateStatusType::REWARDED_FULL,
        winDate: new DateTimeImmutable(),
        reward: $reward2
    );

    $rewardIssuanceState3 = new RewardIssuanceState(
        id: 3,
        calculationResultId: 300,
        program: $program1,
        nomination: $nomination1,
        rewardsCount: 3,
        status: RewardIssuanceStateStatusType::REWARDED_FULL,
        winDate: new DateTimeImmutable(),
        reward: $reward3
    );

    $partner = new Partner(
        id: 1,
        name: 'Test Partner',
        contract: 'CONTRACT123',
        country: $country,
        isFamily: false,
        rewardIssuanceStates: [$rewardIssuanceState1, $rewardIssuanceState2, $rewardIssuanceState3]
    );

    $groupedRewards = $partner->getRewardsGroupedByNomination();

    expect($groupedRewards)->toHaveCount(2);
    expect($groupedRewards[10])->toHaveCount(2);
    expect($groupedRewards[20])->toHaveCount(1);
    expect($groupedRewards[10][0])->toBe($rewardIssuanceState1);
    expect($groupedRewards[10][1])->toBe($rewardIssuanceState3);
    expect($groupedRewards[20][0])->toBe($rewardIssuanceState2);
});

it('gets empty grouped rewards when no reward issuance states', function (): void {
    $country = new Country(1, 'Test Country');

    $partner = new Partner(
        id: 1,
        name: 'Test Partner',
        contract: 'CONTRACT123',
        country: $country,
        isFamily: false
    );

    $groupedRewards = $partner->getRewardsGroupedByNomination();

    expect($groupedRewards)->toBe([]);
});

it('gets tickets when registrations exist', function (): void {
    $country = new Country(1, 'Test Country');
    $registration1 = new Registration(
        id: 1,
        partnerId: 1,
        registrationDate: new DateTimeImmutable('2024-01-01')
    );
    $registration2 = new Registration(
        id: 2,
        partnerId: 1,
        registrationDate: new DateTimeImmutable('2024-01-02')
    );

    $partner = new Partner(
        id: 1,
        name: 'Test Partner',
        contract: 'CONTRACT123',
        country: $country,
        isFamily: false
    );

    $partner->addRegistrations([$registration1, $registration2]);

    $tickets = $partner->getTickets();

    expect($tickets)->not->toBeNull();
    expect($tickets['count'])->toBe(2);
    expect($tickets['registrationDates'])->toBe(['2024-01-01', '2024-01-02']);
});

it('gets tickets with unique registration dates', function (): void {
    $country = new Country(1, 'Test Country');
    $registration1 = new Registration(
        id: 1,
        partnerId: 1,
        registrationDate: new DateTimeImmutable('2024-01-01')
    );
    $registration2 = new Registration(
        id: 2,
        partnerId: 1,
        registrationDate: new DateTimeImmutable('2024-01-01') // Same date
    );
    $registration3 = new Registration(
        id: 3,
        partnerId: 1,
        registrationDate: new DateTimeImmutable('2024-01-02')
    );

    $partner = new Partner(
        id: 1,
        name: 'Test Partner',
        contract: 'CONTRACT123',
        country: $country,
        isFamily: false
    );

    $partner->addRegistrations([$registration1, $registration2, $registration3]);

    $tickets = $partner->getTickets();

    expect($tickets)->not->toBeNull();
    expect($tickets['count'])->toBe(3);
    expect($tickets['registrationDates'])->toContain('2024-01-01');
    expect($tickets['registrationDates'])->toContain('2024-01-02');
    expect(count($tickets['registrationDates']))->toBe(2);
});

it('returns null for tickets when no registrations', function (): void {
    $country = new Country(1, 'Test Country');

    $partner = new Partner(
        id: 1,
        name: 'Test Partner',
        contract: 'CONTRACT123',
        country: $country,
        isFamily: false
    );

    $tickets = $partner->getTickets();

    expect($tickets)->toBe([
        'count'             => 0,
        'registrationDates' => [],
    ]);
});

it('returns null for tickets when empty registrations array', function (): void {
    $country = new Country(1, 'Test Country');

    $partner = new Partner(
        id: 1,
        name: 'Test Partner',
        contract: 'CONTRACT123',
        country: $country,
        isFamily: false
    );

    $partner->addRegistrations([]);

    $tickets = $partner->getTickets();

    expect($tickets)->toBe([
        'count'             => 0,
        'registrationDates' => [],
    ]);
});

it('converts to partner by contract response', function (): void {
    $country = new Country(1, 'Test Country');
    $program = new Program(1, 'Test Program');
    $nomination = new Nomination(10, 'Test Nomination', $program);
    $reward = new Reward(1, 'Test Reward', 1, $nomination);

    $rewardIssuanceState = new RewardIssuanceState(
        id: 1,
        calculationResultId: 100,
        program: $program,
        nomination: $nomination,
        rewardsCount: 2,
        status: RewardIssuanceStateStatusType::NOT_REWARDED,
        winDate: new DateTimeImmutable('2024-01-01'),
        reward: $reward
    );

    $partner = new Partner(
        id: 1,
        name: 'Test Partner',
        contract: 'CONTRACT123',
        country: $country,
        isFamily: true,
        rewardIssuanceStates: [$rewardIssuanceState]
    );

    $result = $partner->toPartnerByContractResponse();

    expect($result)->toHaveCount(1);
    expect($result[0]->id)->toBe(1);
    expect($result[0]->contract->name)->toBe('Test Partner');
    expect($result[0]->contract->contract)->toBe('CONTRACT123');
    expect($result[0]->contract->isFamily)->toBeTrue();
    expect($result[0]->country)->toBe('Test Country');
    expect($result[0]->program)->toBe('Test Program');
    expect($result[0]->nomination->id)->toBe(10);
    expect($result[0]->nomination->name)->toBe('Test Nomination');
    expect($result[0]->awards)->toHaveCount(1);
});

it('converts to partner by contract response with empty rewards', function (): void {
    $country = new Country(1, 'Test Country');

    $partner = new Partner(
        id: 1,
        name: 'Test Partner',
        contract: 'CONTRACT123',
        country: $country,
        isFamily: false
    );

    $result = $partner->toPartnerByContractResponse();

    expect($result)->toBe([]);
});

it('converts to partner by event response', function (): void {
    $country = new Country(1, 'Test Country');
    $program = new Program(1, 'Test Program');
    $nomination = new Nomination(10, 'Test Nomination', $program);
    $reward = new Reward(1, 'Test Reward', 1, $nomination);
    $status = new PartnerStatus(
        id: 1,
        partnerId: 1,
        statusType: PartnerStatusType::TO_AWARD,
        rewardsCount: 5,
        penaltiesCount: 2
    );

    $rewardIssuanceState = new RewardIssuanceState(
        id: 1,
        calculationResultId: 100,
        program: $program,
        nomination: $nomination,
        rewardsCount: 2,
        status: RewardIssuanceStateStatusType::NOT_REWARDED,
        winDate: new DateTimeImmutable('2024-01-01'),
        reward: $reward
    );

    $registration = new Registration(
        id: 1,
        partnerId: 1,
        registrationDate: new DateTimeImmutable('2024-01-01')
    );

    $partner = new Partner(
        id: 1,
        name: 'Test Partner',
        contract: 'CONTRACT123',
        country: $country,
        isFamily: true,
        status: $status,
        rewardIssuanceStates: [$rewardIssuanceState]
    );

    $partner->addRegistrations([$registration]);

    $result = $partner->toPartnerByEventResponse();

    expect($result->id)->toBe(1);
    expect($result->name)->toBe('Test Partner');
    expect($result->contract)->toBe('CONTRACT123');
    expect($result->country->id)->toBe(1);
    expect($result->country->name)->toBe('Test Country');
    expect($result->isFamily)->toBeTrue();
    expect($result->tickets->count)->toBe(1);
    expect($result->tickets->registrationDates)->toBe(['2024-01-01']);
    expect($result->status)->not->toBeNull();
    expect($result->status->id)->toBe(1);
    expect($result->calculationResultIds)->toBe([100]);
    expect($result->rewards)->toHaveCount(1);
    expect($result->rewards[0]->name)->toBe('Test Reward');
    expect($result->rewards[0]->calculationResultId)->toBe(100);
    expect($result->rewards[0]->count)->toBe(2);
});

it('converts to partner by event response with null status', function (): void {
    $country = new Country(1, 'Test Country');

    $partner = new Partner(
        id: 1,
        name: 'Test Partner',
        contract: 'CONTRACT123',
        country: $country,
        isFamily: false
    );

    $result = $partner->toPartnerByEventResponse();

    expect($result->id)->toBe(1);
    expect($result->name)->toBe('Test Partner');
    expect($result->contract)->toBe('CONTRACT123');
    expect($result->country->id)->toBe(1);
    expect($result->country->name)->toBe('Test Country');
    expect($result->isFamily)->toBeFalse();
    expect($result->tickets->count)->toBe(0);
    expect($result->tickets->registrationDates)->toBe([]);
    expect($result->status)->toBeNull();
    expect($result->calculationResultIds)->toBe([]);
    expect($result->rewards)->toBe([]);
});

it('converts to partner full response', function (): void {
    $country = new Country(1, 'Test Country');
    $program = new Program(1, 'Test Program');
    $nomination = new Nomination(10, 'Test Nomination', $program);
    $reward = new Reward(1, 'Test Reward', 1, $nomination);
    $rang = new Rang(
        id: 1,
        rang: 'BR1',
        name: 'Бронзовый',
        date: new DateTimeImmutable('2024-01-01')
    );
    $status = new PartnerStatus(
        id: 1,
        partnerId: 1,
        statusType: PartnerStatusType::TO_AWARD,
        rewardsCount: 5,
        penaltiesCount: 2
    );
    $penalty = new Penalty(
        id: 1,
        name: 'Штраф',
        prim: 'Описание',
        start: new DateTimeImmutable('2024-01-01')
    );

    $rewardIssuanceState = new RewardIssuanceState(
        id: 1,
        calculationResultId: 100,
        program: $program,
        nomination: $nomination,
        rewardsCount: 2,
        status: RewardIssuanceStateStatusType::NOT_REWARDED,
        winDate: new DateTimeImmutable('2024-01-01'),
        reward: $reward
    );

    $registration = new Registration(
        id: 1,
        partnerId: 1,
        registrationDate: new DateTimeImmutable('2024-01-01')
    );

    $partner = new Partner(
        id: 1,
        name: 'Test Partner',
        contract: 'CONTRACT123',
        country: $country,
        isFamily: true,
        rang: $rang,
        status: $status,
        penalties: [$penalty],
        rewardIssuanceStates: [$rewardIssuanceState]
    );

    $partner->addRegistrations([$registration]);

    $result = $partner->toPartnerFullResponse();

    expect($result->id)->toBe(1);
    expect($result->name)->toBe('Test Partner');
    expect($result->contract)->toBe('CONTRACT123');
    expect($result->country->id)->toBe(1);
    expect($result->country->name)->toBe('Test Country');
    expect($result->isFamily)->toBeTrue();
    expect($result->tickets->count)->toBe(1);
    expect($result->tickets->registrationDates)->toBe(['2024-01-01']);
    expect($result->rang)->not->toBeNull();
    expect($result->rang->id)->toBe(1);
    expect($result->rang->rang)->toBe('BR1');
    expect($result->rang->name)->toBe('Бронзовый');
    expect($result->status)->not->toBeNull();
    expect($result->status->id)->toBe(1);
    expect($result->penalties)->toHaveCount(1);
    expect($result->penalties[0]->id)->toBe(1);
    expect($result->penalties[0]->name)->toBe('Штраф');
    expect($result->rewardIssuanceState)->toHaveCount(1);
    expect($result->rewardIssuanceState[0]->id)->toBe(1);
    expect($result->rewardIssuanceState[0]->calculationResult->id)->toBe(100);
});

it('converts to partner full response with null optional fields', function (): void {
    $country = new Country(1, 'Test Country');

    $partner = new Partner(
        id: 1,
        name: 'Test Partner',
        contract: 'CONTRACT123',
        country: $country,
        isFamily: false
    );

    $result = $partner->toPartnerFullResponse();

    expect($result->id)->toBe(1);
    expect($result->name)->toBe('Test Partner');
    expect($result->contract)->toBe('CONTRACT123');
    expect($result->country->id)->toBe(1);
    expect($result->country->name)->toBe('Test Country');
    expect($result->isFamily)->toBeFalse();
    expect($result->tickets->count)->toBe(0);
    expect($result->tickets->registrationDates)->toBe([]);
    expect($result->rang)->toBeNull();
    expect($result->status)->toBeNull();
    expect($result->penalties)->toBe([]);
    expect($result->rewardIssuanceState)->toBe([]);
});
