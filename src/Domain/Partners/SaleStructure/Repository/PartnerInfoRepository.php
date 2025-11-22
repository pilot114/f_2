<?php

declare(strict_types=1);

namespace App\Domain\Partners\SaleStructure\Repository;

use App\Domain\Partners\SaleStructure\Entity\PartnerInfo;
use Database\Connection\ParamMode;
use Database\Connection\ParamType;
use Database\ORM\QueryRepository;

/**
 * @extends QueryRepository<PartnerInfo>
 */
class PartnerInfoRepository extends QueryRepository
{
    protected string $entityName = PartnerInfo::class;
    public function getEmployeeByContract(string $contract): ?PartnerInfo
    {
        $sql = "SELECT
                e.id,
                e.name,
                e.contract,
                c.tehno_id country_code,
                e.d_end
                FROM net.employee e
                LEFT JOIN net.country c
                    ON e.country = c.id
                WHERE contract = :contract";
        return $this->query($sql, [
            'contract' => $contract,
        ])->first();
    }

    public function getEmployeeInfo(?int $id): array
    {
        return $this->conn->procedure(
            'net.web_cursors.cr_personal_lider_info',
            [
                'o_result' => null,
                'i_emp_id' => $id,
            ],
            [
                'o_result' => [ParamMode::OUT, ParamType::CURSOR],
                'i_emp_id' => [ParamMode::IN, ParamType::INTEGER],
            ]
        );

    }

    public function getRankNameById(int $id): ?string
    {
        $sql = "SELECT r.short_name name FROM net.rang r WHERE r.rang = $id";
        return $this->conn->query($sql)->current()['name'];
    }

}
