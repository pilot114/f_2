<?php

declare(strict_types=1);

namespace App\Tests\Unit\Events\Rewards\UseCase;

use App\Domain\Events\Rewards\Entity\Nomination;
use App\Domain\Events\Rewards\Entity\Program;
use App\Domain\Events\Rewards\Entity\Reward;
use App\Domain\Events\Rewards\Repository\RewardCommandRepository;
use App\Domain\Events\Rewards\Repository\RewardQueryRepository;
use App\Domain\Events\Rewards\UseCase\AddCommentToRewardUseCase;
use Mockery;

it('add comment to reward', function (): void {
    $readRewardRepository = Mockery::mock(RewardQueryRepository::class);
    $writeRewardRepository = Mockery::mock(RewardCommandRepository::class);
    $useCase = new AddCommentToRewardUseCase($readRewardRepository, $writeRewardRepository);

    $rewardId = 1;
    $comment = 'comment';
    $reward = new Reward($rewardId, 'reward name', 1, new Nomination(1,'Номинация 1',new Program(1, 'Программа 1')));

    $readRewardRepository->shouldReceive('getOne')->with($rewardId)->once()->andReturn($reward);
    $writeRewardRepository->shouldReceive('addCommentToReward')->with($reward)->once();
    ##########################################
    $useCase->addCommentToReward($rewardId, $comment);
    ##########################################
    expect($reward->getComment())->toBe($comment);
});
