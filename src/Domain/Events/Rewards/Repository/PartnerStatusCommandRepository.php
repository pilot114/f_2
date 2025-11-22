<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\Repository;

use App\Domain\Events\Rewards\Entity\PartnerStatus;
use Database\Connection\ParamType;
use Database\ORM\CommandRepository;
use DateTimeImmutable;

/**
 * @extends CommandRepository<PartnerStatus>
 */
class PartnerStatusCommandRepository extends CommandRepository
{
    protected string $entityName = PartnerStatus::class;

    public function createStatus(PartnerStatus $status): int
    {
        return $this->conn->insert('NET.PD_EMPLOYEE_STATUS',
            [
                'EMPLOYEE_ID'   => $status->partnerId,
                'PD_STATUS_ID'  => $status->getStatusType()->value,
                'REWARD_COUNT'  => $status->getRewardsCount(),
                'PENALTY_COUNT' => $status->getPenaltiesCount(),
                'DT_FROM'       => new DateTimeImmutable(),
                'CP_EMP'        => $status->getUser()?->id, // TODO ТУТ НУЖЕН ЮЗЕР
            ],
            [
                'DT_FROM' => ParamType::DATE,
            ]
        );
    }

    public function updateStatus(PartnerStatus $status): int
    {
        return $this->conn->update('NET.PD_EMPLOYEE_STATUS',
            [
                'PD_STATUS_ID'  => $status->getStatusType()->value,
                'REWARD_COUNT'  => $status->getRewardsCount(),
                'PENALTY_COUNT' => $status->getPenaltiesCount(),
                'DT_FROM'       => new DateTimeImmutable(),
                'CP_EMP'        => $status->getUser()?->id, // TODO ТУТ НУЖЕН ЮЗЕР
            ],
            [
                'EMPLOYEE_ID' => $status->partnerId,
            ],
            [
                'DT_FROM' => ParamType::DATE,
            ]
        );
    }

    public function updateCountsOnly(PartnerStatus $status): int
    {
        return $this->conn->update('NET.PD_EMPLOYEE_STATUS',
            [
                'REWARD_COUNT'  => $status->getRewardsCount(),
                'PENALTY_COUNT' => $status->getPenaltiesCount(),
            ],
            [
                'EMPLOYEE_ID' => $status->partnerId,
            ],
            [
                'DT_FROM' => ParamType::DATE,
            ]
        );
    }
}
