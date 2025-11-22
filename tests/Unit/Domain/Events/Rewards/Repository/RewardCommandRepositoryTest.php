<?php

declare(strict_types=1);

namespace App\Tests\Unit\Events\Rewards\Repository;

use App\Domain\Events\Rewards\Entity\Nomination;
use App\Domain\Events\Rewards\Entity\Program;
use App\Domain\Events\Rewards\Entity\Reward;
use App\Domain\Events\Rewards\Repository\RewardCommandRepository;
use Database\Connection\WriteDatabaseInterface;
use Mockery;

it('add comment to reward', function (): void {
    $connection = Mockery::mock(WriteDatabaseInterface::class);
    $repository = new RewardCommandRepository($connection, getDataMapper());

    $reward = new Reward(1, 'тестовая награда 1', 1, new Nomination(1,'Номинация 1',new Program(1, 'Программа 1')));
    $reward->setComment('Комментарий');

    $connection->shouldReceive('update')
        ->once()
        ->withArgs(function ($sql, array $data, array $condition) use ($reward): bool {
            return $data['commentary'] === $reward->getComment()
                && $condition['id'] === $reward->id;
        });

    $repository->addCommentToReward($reward);
});
