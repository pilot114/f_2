<?php

declare(strict_types=1);

namespace App\Tests\Unit\Events\Rewards\Entity;

use App\Domain\Events\Rewards\Entity\Nomination;
use App\Domain\Events\Rewards\Entity\Program;
use App\Domain\Events\Rewards\Entity\Reward;
use App\Domain\Events\Rewards\Entity\RewardIssuanceState;
use App\Domain\Events\Rewards\Entity\User;
use App\Domain\Events\Rewards\Enum\RewardIssuanceStateStatusType;
use DateTimeImmutable;

function createRewardIssuanceState(): RewardIssuanceState
{
    $program = new Program(1, 'Тестовая программа');
    $nomination = new Nomination(1, 'Тестовая номинация', $program);
    $reward = new Reward(1, 'Тестовая награда', 100, $nomination);

    return new RewardIssuanceState(
        id: 1,
        calculationResultId: 100,
        program: $program,
        nomination: $nomination,
        rewardsCount: 2,
        status: RewardIssuanceStateStatusType::NOT_REWARDED,
        winDate: new DateTimeImmutable('2024-01-01'),
        reward: $reward
    );
}

it('creates reward issuance state', function (): void {
    $program = new Program(1, 'Тестовая программа');
    $nomination = new Nomination(1, 'Тестовая номинация', $program);
    $reward = new Reward(1, 'Тестовая награда', 100, $nomination);
    $winDate = new DateTimeImmutable('2024-01-01');

    $state = new RewardIssuanceState(
        id: 1,
        calculationResultId: 100,
        program: $program,
        nomination: $nomination,
        rewardsCount: 2,
        status: RewardIssuanceStateStatusType::NOT_REWARDED,
        winDate: $winDate,
        reward: $reward
    );

    expect($state->id)->toBe(1);
    expect($state->calculationResultId)->toBe(100);
    expect($state->rewardsCount)->toBe(2);
    expect($state->winDate)->toBe($winDate);
    expect($state->getStatus())->toBe(RewardIssuanceStateStatusType::NOT_REWARDED);
});

it('sets and gets status', function (): void {
    $state = createRewardIssuanceState();

    $state->setStatus(RewardIssuanceStateStatusType::REWARDED_FULL);

    expect($state->getStatus())->toBe(RewardIssuanceStateStatusType::REWARDED_FULL);
});

it('sets and gets event id', function (): void {
    $state = createRewardIssuanceState();

    expect($state->getEventId())->toBeNull();

    $state->setEventId(123);

    expect($state->getEventId())->toBe(123);
});

it('sets and gets reward date', function (): void {
    $state = createRewardIssuanceState();
    $rewardDate = new DateTimeImmutable('2024-02-01');

    expect($state->getRewardDate())->toBeNull();

    $state->setRewardDate($rewardDate);

    expect($state->getRewardDate())->toBe($rewardDate);
});

it('sets and gets rewarded by user', function (): void {
    $state = createRewardIssuanceState();
    $user = new User(1, 'Администратор');

    expect($state->getRewardedByUser())->toBeNull();

    $state->setRewardedByUser($user);

    expect($state->getRewardedByUser())->toBe($user);
});

it('sets and gets note', function (): void {
    $state = createRewardIssuanceState();

    expect($state->getNote())->toBeNull();

    $state->setNote('Тестовая заметка');

    expect($state->getNote())->toBe('Тестовая заметка');
});

it('converts to array', function (): void {
    $program = new Program(1, 'Тестовая программа');
    $nomination = new Nomination(1, 'Тестовая номинация', $program);
    $reward = new Reward(1, 'Тестовая награда', 100, $nomination);
    $winDate = new DateTimeImmutable('2024-01-01');
    $rewardDate = new DateTimeImmutable('2024-02-01');
    $user = new User(1, 'Администратор');

    $state = new RewardIssuanceState(
        id: 1,
        calculationResultId: 100,
        program: $program,
        nomination: $nomination,
        rewardsCount: 2,
        status: RewardIssuanceStateStatusType::REWARDED_FULL,
        winDate: $winDate,
        reward: $reward,
        note: 'Тестовая заметка'
    );

    $state->setRewardDate($rewardDate);
    $state->setRewardedByUser($user);

    $result = $state->toRewardIssuanceStateResponse();

    expect($result->id)->toBe(1);
    expect($result->calculationResult->id)->toBe(100);
    expect($result->rewardsCount)->toBe(2);
    expect($result->winDate)->toBe($winDate->format(DateTimeImmutable::ATOM));
    expect($result->rewardDate)->toBe($rewardDate->format(DateTimeImmutable::ATOM));
    expect($result->note)->toBe('Тестовая заметка');
    expect($result->status->id)->toBe(1);
    expect($result->status->name)->toBe('Выдан полностью');
    expect($result->rewardedByUser->id)->toBe($user->id);
    expect($result->rewardedByUser->name)->toBe($user->name);
});

it('converts to reward issuance state response with nulls', function (): void {
    $state = createRewardIssuanceState();

    $result = $state->toRewardIssuanceStateResponse();

    expect($result->rewardDate)->toBeNull();
    expect($result->rewardedByUser)->toBeNull();
    expect($result->note)->toBeNull();
});
