<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\Repository;

use App\Domain\Events\Rewards\Entity\RewardIssuanceState;
use Database\Connection\ParamType;
use Database\ORM\CommandRepository;

/**
 * @extends CommandRepository<RewardIssuanceState>
 */
class RewardIssuanceStateCommandRepository extends CommandRepository
{
    protected string $entityName = RewardIssuanceState::class;

    public function setStatus(RewardIssuanceState $rewardIssuanceState): void
    {
        $values = [
            'REWARD_DATE' => $rewardIssuanceState->getRewardDate(),
            'IS_REWARD'   => $rewardIssuanceState->getStatus()->value,
            'REWARD_USER' => $rewardIssuanceState->getRewardedByUser()?->id,
            // 'CURR_ID'  => $userId, TODO РАЗОБРАТЬСЯ ЧТО ЭТО ТАКОЕ И ОТКУДА ЭТО БРАТЬ
            // 'SPIS_ID'  => $userId, TODO РАЗОБРАТЬСЯ ЧТО ЭТО ТАКОЕ И ОТКУДА ЭТО БРАТЬ
        ];

        if ($rewardIssuanceState->getNote() !== null && $rewardIssuanceState->getNote() !== '' && $rewardIssuanceState->getNote() !== '0') {
            $values['NOTE'] = $rewardIssuanceState->getNote();
        }
        if (!in_array($rewardIssuanceState->getEventId(), [null, 0], true)) {
            $values['CELEB_ID'] = $rewardIssuanceState->getEventId();
        }

        $this->conn->update('NET.PD_REWARD_STATUS',
            $values,
            [
                'id' => $rewardIssuanceState->id,
            ],
            [
                'REWARD_DATE' => ParamType::DATE,
            ]
        );
    }

    public function setComment(RewardIssuanceState $rewardIssuanceState): void
    {
        $values = [
            'REWARD_DATE' => $rewardIssuanceState->getRewardDate(),
            'REWARD_USER' => $rewardIssuanceState->getRewardedByUser()?->id,
            'NOTE'        => $rewardIssuanceState->getNote(),
        ];

        $this->conn->update('NET.PD_REWARD_STATUS',
            $values,
            [
                'id' => $rewardIssuanceState->id,
            ],
            [
                'REWARD_DATE' => ParamType::DATE,
            ]
        );
    }
}
