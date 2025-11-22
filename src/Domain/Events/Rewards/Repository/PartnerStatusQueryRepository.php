<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\Repository;

use App\Domain\Events\Rewards\Entity\PartnerStatus;
use App\Domain\Events\Rewards\Enum\PartnerStatusType;
use Database\ORM\QueryRepository;

/**
 * @extends QueryRepository<PartnerStatus>
 */
class PartnerStatusQueryRepository extends QueryRepository
{
    public const CALCULATE_STATUS = 1;
    public const CALCULATE_PENALTIES_COUNT = 2;
    public const CALCULATE_REWARDS_COUNT = 3;

    protected string $entityName = PartnerStatus::class;

    public function getActualStatusType(int $partnerId): ?PartnerStatusType
    {
        /** @var string $result */
        $result = $this->conn->function("net.calc_pd_employee_status", [
            'pEmployee_id' => $partnerId,
            'ptype'        => self::CALCULATE_STATUS,
        ]);

        return PartnerStatusType::tryFrom((int) $result);
    }

    public function getActualRewardCount(int $partnerId): int
    {
        /** @var string $result */
        $result = $this->conn->function(
            "net.calc_pd_employee_status",
            [
                'pEmployee_id' => $partnerId,
                'ptype'        => self::CALCULATE_REWARDS_COUNT,
            ]
        );

        return (int) $result;
    }

    public function getActualPenaltiesCount(int $partnerId): int
    {
        /** @var string $result */
        $result = $this->conn->function(
            "net.calc_pd_employee_status",
            [
                'pEmployee_id' => $partnerId,
                'ptype'        => self::CALCULATE_PENALTIES_COUNT,
            ]
        );

        return (int) $result;
    }

    public function getPartnerSavedStatus(int $partnerId): ?PartnerStatus
    {
        $sql = "
            select
                emp_st.id id,
                emp_st.employee_id partner_id,
                emp_st.pd_status_id pd_status_id,
                emp_st.reward_count eward_count,
                emp_st.penalty_count penalty_count     
            from net.pd_employee_status emp_st 
            where 1=1
            and emp_st.employee_id = :partnerId
        ";

        return $this->query($sql, [
            'partnerId' => $partnerId,
        ])->first();
    }
}
