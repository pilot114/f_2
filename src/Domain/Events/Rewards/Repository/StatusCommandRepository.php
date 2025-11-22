<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\Repository;

use App\Domain\Events\Rewards\Entity\Reward;
use App\Domain\Events\Rewards\Entity\RewardStatus;
use Database\Connection\ParamType;
use Database\ORM\CommandRepository;
use DateTimeImmutable;

/**
 * @extends CommandRepository<RewardStatus>
 */
class StatusCommandRepository extends CommandRepository
{
    protected string $entityName = RewardStatus::class;

    public function createStatusInCountry(Reward $reward, RewardStatus $status, int $userId): void
    {
        $this->conn->insert('NET.PD_PRESENT_GDS_COUNTRY_STATUS',
            [
                'pd_present_gds_id' => $reward->id,
                'country_id'        => $status->getCountryId(),
                'status'            => $status->getStatusId(),
                'dt_from'           => new DateTimeImmutable(),
                'cp_emp'            => $userId,
            ],
            [
                'dt_from' => ParamType::DATE,
            ]
        );
    }

    public function updateStatusInCountry(Reward $reward, RewardStatus $status, int $userId): void
    {
        $this->conn->update('NET.PD_PRESENT_GDS_COUNTRY_STATUS',
            [
                'status'  => $status->getStatusId(),
                'dt_from' => new DateTimeImmutable(),
                'cp_emp'  => $userId,
            ],
            [
                'pd_present_gds_id' => $reward->id,
                'country_id'        => $status->getCountryId(),
            ],
            [
                'dt_from' => ParamType::DATE,
            ]
        );
    }
}
