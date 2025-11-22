<?php

declare(strict_types=1);

namespace App\Tests\Unit\Events\Rewards\Entity;

use App\Domain\Events\Rewards\DTO\NominationWithRewardsResponse;
use App\Domain\Events\Rewards\DTO\RewardFullResponse;
use App\Domain\Events\Rewards\Entity\Country;
use App\Domain\Events\Rewards\Entity\Nomination;
use App\Domain\Events\Rewards\Entity\Program;
use App\Domain\Events\Rewards\Entity\Reward;
use App\Domain\Events\Rewards\Entity\RewardStatus;
use App\Domain\Events\Rewards\Entity\RewardType;
use App\Domain\Events\Rewards\Enum\RewardStatusType;

it('creates nomination', function (): void {
    $program = new Program(1, 'Тестовая программа');
    $nomination = new Nomination(1, 'Тестовая номинация', $program, []);

    expect($nomination->id)->toBe(1);
    expect($nomination->name)->toBe('Тестовая номинация');
    expect($nomination->getProgram())->toBe($program);
});

it('gets rewards', function (): void {
    $program = new Program(1, 'Тестовая программа');
    $nomination = new Nomination(1, 'Тестовая номинация', $program, []);

    $country1 = new Country(1, 'Country 1');
    $country2 = new Country(2, 'Country 2');
    $rewardStatus1 = new RewardStatus(1, RewardStatusType::ACTIVE, $country1);
    $rewardStatus2 = new RewardStatus(2, RewardStatusType::ARCHIVE, $country2);

    $reward1 = new Reward(1, 'Награда 1', 100, $nomination, null, new RewardType(1, 'Type 1'), [$rewardStatus1]);
    $reward2 = new Reward(2, 'Награда 2', 200, $nomination, null, new RewardType(2, 'Type 2'), [$rewardStatus2]);

    $nomination->setRewards([
        1 => $reward1,
        2 => $reward2,
    ]);

    $rewards = $nomination->getRewards();

    expect($rewards)->toHaveCount(2);
    expect($rewards[0]->name)->toBe('Награда 1');
    expect($rewards[1]->name)->toBe('Награда 2');
});

it('gets reward name by id', function (): void {
    $program = new Program(1, 'Тестовая программа');
    $nomination = new Nomination(1, 'Тестовая номинация', $program, []);

    $reward = new Reward(1, 'Награда 1', 100, $nomination, null, new RewardType(1, 'Type 1'));
    $nomination->setRewards([
        1 => $reward,
    ]);

    expect($nomination->getRewardNameByRewardId(1))->toBe('Награда 1');
    expect($nomination->getRewardNameByRewardId(999))->toBeNull();
});

it('converts to array', function (): void {
    $program = new Program(1, 'Тестовая программа');
    $nomination = new Nomination(1, 'Тестовая номинация', $program, []);

    $reward1 = new Reward(1, 'Награда 1', 100, $nomination, null, new RewardType(1, 'Type 1'));
    $reward2 = new Reward(2, 'Награда 2', 200, $nomination, null, new RewardType(2, 'Type 2'));

    $nomination->setRewards([
        1 => $reward1,
        2 => $reward2,
    ]);

    $result = $nomination->toNominationWithRewardsResponse();

    expect($result->id)->toBe(1);
    expect($result->name)->toBe('Тестовая номинация');
    expect($result->rewards_count)->toBe(2);
    expect($result->rewards)->toHaveCount(2);
});

it('converts to array without rewards', function (): void {
    $program = new Program(1, 'Тестовая программа');
    $nomination = new Nomination(1, 'Тестовая номинация', $program, []);

    $result = $nomination->toNominationWithRewardsResponse();

    expect($result->rewards_count)->toBe(0);
    expect($result->rewards)->toBeEmpty();
});

it('toNominationWithRewardsResponse creates correct DTO', function (): void {
    $program = new Program(1, 'Test Program');
    $nomination = new Nomination(1, 'Test Nomination', $program, []);

    $country1 = new Country(1, 'Country 1');
    $country2 = new Country(2, 'Country 2');
    $rewardStatus1 = new RewardStatus(1, RewardStatusType::ACTIVE, $country1);
    $rewardStatus2 = new RewardStatus(2, RewardStatusType::ARCHIVE, $country2);

    $reward1 = new Reward(1, 'Reward 1', 100, $nomination, 'Comment 1', new RewardType(1, 'Type 1'), [$rewardStatus1]);
    $reward2 = new Reward(2, 'Reward 2', 200, $nomination, 'Comment 2', new RewardType(2, 'Type 2'), [$rewardStatus2]);

    $nomination->setRewards([
        1 => $reward1,
        2 => $reward2,
    ]);

    $response = $nomination->toNominationWithRewardsResponse();

    expect($response)->toBeInstanceOf(NominationWithRewardsResponse::class)
        ->and($response->id)->toBe(1)
        ->and($response->name)->toBe('Test Nomination')
        ->and($response->rewards_count)->toBe(2)
        ->and($response->rewards)->toHaveCount(2)
        ->and($response->rewards[0])->toBeInstanceOf(RewardFullResponse::class)
        ->and($response->rewards[0]->name)->toBe('Reward 1')
        ->and($response->rewards[1]->name)->toBe('Reward 2');
});
