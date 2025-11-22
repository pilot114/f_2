<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\Repository;

use App\Domain\Events\Rewards\Entity\Reward;
use Database\ORM\CommandRepository;

/**
 * @extends CommandRepository<Reward>
 */
class RewardCommandRepository extends CommandRepository
{
    protected string $entityName = Reward::class;

    public function addCommentToReward(Reward $reward): void
    {
        $this->conn->update(
            'NET.PD_PRESENT_GDS',
            [
                'commentary' => $reward->getComment(),
            ],
            [
                'id' => $reward->id,
            ]
        );
    }

    public function changeRewardType(int $productId, int $typeId): void
    {
        $this->conn->update(
            'NET.PD_GDS_REWARD_TYPES',
            [
                'REWARD_TYPE_ID' => $typeId,
            ],
            [
                'GDS_ID' => $productId,
            ],
        );
    }

    public function addRewardType(int $productId, int $typeId): void
    {
        $this->conn->insert(
            'NET.PD_GDS_REWARD_TYPES',
            [
                'GDS_ID'         => $productId,
                'REWARD_TYPE_ID' => $typeId,
            ],
        );
    }

    public function deleteRewardType(int $productId): void
    {
        $this->conn->delete(
            'NET.PD_GDS_REWARD_TYPES',
            [
                'GDS_ID' => $productId,
            ],
        );
    }
}
