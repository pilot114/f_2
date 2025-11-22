<?php

declare(strict_types=1);

namespace App\Tests\Unit\Events\Rewards\Repository;

use App\Domain\Events\Rewards\Entity\Nomination;
use App\Domain\Events\Rewards\Entity\Program;
use App\Domain\Events\Rewards\Entity\Reward;
use App\Domain\Events\Rewards\Entity\RewardIssuanceState;
use App\Domain\Events\Rewards\Entity\User;
use App\Domain\Events\Rewards\Enum\RewardIssuanceStateStatusType;
use App\Domain\Events\Rewards\Repository\RewardIssuanceStateCommandRepository;
use Database\Connection\ParamType;
use Database\Connection\WriteDatabaseInterface;
use DateTimeImmutable;
use Mockery;

it('set status', function (): void {

    $connection = Mockery::mock(WriteDatabaseInterface::class);
    $repository = new RewardIssuanceStateCommandRepository($connection, getDataMapper());

    $program = new Program(
        id: 1,
        name: 'программа'
    );
    $nomination = new Nomination(
        id: 1,
        name: 'номинация',
        program: $program
    );
    $rewardIssuanceState = new RewardIssuanceState(
        id: 1,
        calculationResultId: 1,
        program: $program,
        nomination: $nomination,
        rewardsCount: 1,
        status: RewardIssuanceStateStatusType::REWARDED_FULL,
        winDate: new DateTimeImmutable('2024-10-01'),
        reward: new Reward(
            id: 1,
            name: 'значок',
            productId: 1,
            nomination: $nomination
        ),
        note: 'заметка',
        rewardDate: new DateTimeImmutable('2024-10-01'),
        rewardedByUser: new User(
            id: 1, name: "ФИО"
        )
    );
    $rewardIssuanceState->setEventId(1);

    $connection->shouldReceive('update')
        ->once()
        ->withArgs(function ($tableName, array $values, array $criteria, array $type) use ($rewardIssuanceState): bool {
            return $values['REWARD_DATE'] === $rewardIssuanceState->getRewardDate()
                && $values['IS_REWARD'] === $rewardIssuanceState->getStatus()->value
                && $values['REWARD_USER'] === $rewardIssuanceState->getRewardedByUser()?->id
                && $values['NOTE'] === $rewardIssuanceState->getNote()
                && $values['CELEB_ID'] === $rewardIssuanceState->getEventId()
                && $criteria['id'] === $rewardIssuanceState->id
                && $type['REWARD_DATE'] === ParamType::DATE;
        });

    $repository->setStatus($rewardIssuanceState);

})->with();
