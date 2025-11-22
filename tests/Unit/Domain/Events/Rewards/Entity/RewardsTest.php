<?php

declare(strict_types=1);

namespace App\Tests\Unit\Events\Rewards\Entity;

use App\Domain\Events\Rewards\DTO\RewardFullResponse;
use App\Domain\Events\Rewards\DTO\RewardTypeResponse;
use App\Domain\Events\Rewards\Entity\Country;
use App\Domain\Events\Rewards\Entity\Nomination;
use App\Domain\Events\Rewards\Entity\Program;
use App\Domain\Events\Rewards\Entity\Reward;
use App\Domain\Events\Rewards\Entity\RewardStatus;
use App\Domain\Events\Rewards\Entity\RewardType;
use App\Domain\Events\Rewards\Enum\RewardStatusType;

it('create reward', function (): void {
    $programId = 1;
    $programName = 'test';
    $program = new Program($programId, $programName);

    $nominationId = 1;
    $nominationName = 'test';
    $nomination = new Nomination($nominationId, $nominationName, $program, []);

    $rewardId = 1;
    $productId = 1;
    $rewardName = 'test';
    $rewardType = new RewardType(1, 'Test Type');
    $reward = new Reward($rewardId, $rewardName, $productId, $nomination, null, $rewardType);

    $comment = 'some comment';
    $reward->setComment($comment);

    $rewardArray = $reward->toArray();

    expect($reward->findStatusInCountry(new Country(1, 'Name')))->toBeNull();
    expect($reward->name)->toBe($rewardName);
    expect($reward->getComment())->toBe($comment);
    expect($reward->getNomination()->id)->toBe($nominationId);
    expect($reward->getNomination()->getProgram()->id)->toBe($programId);
    expect($rewardArray['id'])->toBe($rewardId);
    expect($rewardArray['name'])->toBe($rewardName);
    expect($rewardArray['commentary'])->toBe($comment);
    expect($rewardArray['statuses'])->toBe([]);
});

it('toRewardFullResponse creates correct DTO', function (): void {
    $program = new Program(1, 'Test Program');
    $nomination = new Nomination(1, 'Test Nomination', $program, []);

    $country1 = new Country(1, 'Country 1');
    $country2 = new Country(2, 'Country 2');
    $rewardStatus1 = new RewardStatus(1, RewardStatusType::ACTIVE, $country1);
    $rewardStatus2 = new RewardStatus(2, RewardStatusType::ARCHIVE, $country2);

    $rewardType = new RewardType(1, 'Test Type');
    $reward = new Reward(
        1,
        'Test Reward',
        123,
        $nomination,
        'Test Comment',
        $rewardType,
        [$rewardStatus1, $rewardStatus2]
    );

    $response = $reward->toRewardFullResponse();

    expect($response)->toBeInstanceOf(RewardFullResponse::class)
        ->and($response->id)->toBe(1)
        ->and($response->name)->toBe('Test Reward')
        ->and($response->commentary)->toBe('Test Comment')
        ->and($response->statuses)->toHaveCount(2)
        ->and($response->type)->toBeInstanceOf(RewardTypeResponse::class)
        ->and($response->type->name)->toBe('Test Type');
});
