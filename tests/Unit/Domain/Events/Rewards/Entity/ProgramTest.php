<?php

declare(strict_types=1);

namespace App\Tests\Unit\Events\Rewards\Entity;

use App\Domain\Events\Rewards\DTO\NominationWithRewardsResponse;
use App\Domain\Events\Rewards\DTO\ProgramWithNominationsResponse;
use App\Domain\Events\Rewards\Entity\Country;
use App\Domain\Events\Rewards\Entity\Nomination;
use App\Domain\Events\Rewards\Entity\Program;
use App\Domain\Events\Rewards\Entity\Reward;
use App\Domain\Events\Rewards\Entity\RewardStatus;
use App\Domain\Events\Rewards\Entity\RewardType;
use App\Domain\Events\Rewards\Enum\RewardStatusType;

it('create program', function (): void {
    $programId = 1;
    $programName = 'test';
    $program = new Program($programId, $programName, []);
    $programResponse = $program->toProgramWithNominationsResponse();

    expect($programResponse->id)->toBe($programId);
    expect($programResponse->name)->toBe($programName);
    expect($programResponse->nominations_count)->toBe(0);
    expect($programResponse->nominations)->toBe([]);
});

it('toProgramWithNominationsResponse creates correct DTO', function (): void {
    $program = new Program(1, 'Test Program', []);

    $nomination1 = new Nomination(1, 'Nomination 1', $program, []);
    $nomination2 = new Nomination(2, 'Nomination 2', $program, []);

    $country1 = new Country(1, 'Country 1');
    $country2 = new Country(2, 'Country 2');
    $rewardStatus1 = new RewardStatus(1, RewardStatusType::ACTIVE, $country1);
    $rewardStatus2 = new RewardStatus(2, RewardStatusType::ARCHIVE, $country2);

    $reward1 = new Reward(1, 'Reward 1', 100, $nomination1, null, new RewardType(1, 'Type 1'), [$rewardStatus1]);
    $reward2 = new Reward(2, 'Reward 2', 200, $nomination2, null, new RewardType(2, 'Type 2'), [$rewardStatus2]);

    $nomination1->setRewards([
        1 => $reward1,
    ]);
    $nomination2->setRewards([
        2 => $reward2,
    ]);

    $program->setNominations([$nomination1, $nomination2]);

    $response = $program->toProgramWithNominationsResponse();

    expect($response)->toBeInstanceOf(ProgramWithNominationsResponse::class)
        ->and($response->id)->toBe(1)
        ->and($response->name)->toBe('Test Program')
        ->and($response->nominations_count)->toBe(2)
        ->and($response->nominations)->toHaveCount(2)
        ->and($response->nominations[0])->toBeInstanceOf(NominationWithRewardsResponse::class)
        ->and($response->nominations[0]->name)->toBe('Nomination 1')
        ->and($response->nominations[1]->name)->toBe('Nomination 2');
});
