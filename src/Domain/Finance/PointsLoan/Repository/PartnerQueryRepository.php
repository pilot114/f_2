<?php

declare(strict_types=1);

namespace App\Domain\Finance\PointsLoan\Repository;

use App\Domain\Finance\PointsLoan\Entity\Partner;
use Database\EntityNotFoundDatabaseException;
use Database\ORM\QueryRepository;

/** @extends QueryRepository<Partner> */
class PartnerQueryRepository extends QueryRepository
{
    protected string $entityName = Partner::class;

    public function getPartnerStats(string $contract): Partner
    {
        $sql = <<<HEREDOC
                SELECT
                  e.id 
                , e.name name
                , e.contract contract
                , e.d_start
                , e.d_end
                ----------------------------------
                , cm.country country_id
                , c.name country_name
                ----------------------------------
                , cm.dt || cm.emp_id partner_stat_id
                , cm.dt partner_stat_month
                , cm.lo partner_stat_personal_volume
                , cm.oo partner_stat_total_volume
                , cm.rang partner_stat_rang    
                -----------------------------------
                , b.blacklist violation_id
                , b.shtraf violation_type
                , b.prim violation_commentary
                FROM net.employee e
                LEFT JOIN net.cache_main cm ON cm.emp_id = e.id AND cm.dt >= ADD_MONTHS(TRUNC(SYSDATE, 'MM'), -12)
                LEFT JOIN net.country c ON c.id = cm.country
                LEFT JOIN tehno.blacklist b ON b.contract = e.contract AND b.shtraf in (41, 42)
                WHERE 1=1
                AND e.contract = :pContract
                ORDER BY cm.dt asc
                HEREDOC;

        $partner = $this->query($sql, [
            'pContract' => $contract,
        ])->first();

        if ($partner === null) {
            throw new EntityNotFoundDatabaseException('не найден партнёр с контрактом ' . $contract);
        }

        return $partner;
    }

    /** @return string[] */
    public function getPartnerEmails(int $employeeId): array
    {
        $sql = <<<HEREDOC
                SELECT DISTINCT c.mail email
                FROM (SELECT s.mail, s.contract
                      FROM inet.send s 
                      UNION ALL
                      SELECT pcc.contact mail, pcc.contract
                      FROM test.nc_product_client_connect  pcc 
                      WHERE pcc.connect_type = 1 
                             AND pcc.main= 1
                             AND pcc.dt_verified IS NOT NULL ) c
                JOIN net.employee e ON e.contract = c.contract
                WHERE e.id = :employee_id
                HEREDOC;

        $raw = $this->conn->query($sql, [
            'employee_id' => $employeeId,
        ]);

        return array_column(iterator_to_array($raw), 'email');
    }

    public function existsOrFail(int $partnerId, string $message): void
    {
        $sql = "
            SELECT e.id
            from net.employee e
            where
            e.id = :partnerId
            and e.d_end is null
        ";

        $raw = $this->conn->query($sql, [
            'partnerId' => $partnerId,
        ]);

        if (iterator_to_array($raw) === []) {
            throw new EntityNotFoundDatabaseException($message);
        }
    }
}
