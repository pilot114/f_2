<?php

declare(strict_types=1);

namespace App\Domain\Finance\PointsLoan\Repository;

use App\Domain\Finance\PointsLoan\Entity\ExcelLoanRepresentation;
use App\Domain\Finance\PointsLoan\Entity\Loan;
use Database\Connection\ParamType;
use Database\EntityNotFoundDatabaseException;
use Database\ORM\QueryRepository;
use DateTimeImmutable;
use Illuminate\Support\Enumerable;

/** @extends QueryRepository<Loan> */
class LoanQueryRepository extends QueryRepository
{
    protected string $entityName = Loan::class;

    /** @return Enumerable<int, Loan> */
    public function getHistory(int $partnerId): Enumerable
    {
        $sql = "SELECT
                  epc.id
                , epc.newspis_id
                , epc.employee_id
                , epc.start_date
                , epc.end_date
                , epc.summ
                , epc.months
                , epc.month_payment
                , e.id guarantor_id
                , e.contract guarantor_contract
                , epc.paid_summ
                FROM net.employee_point_credits epc
                left join net.employee e on e.id = epc.guarantor
                WHERE epc.employee_id = :employee_id
                ORDER BY epc.start_date ASC, epc.id DESC ";

        return $this->query($sql, [
            'employee_id' => $partnerId,
        ]);
    }

    public function getCurrentPeriod(): DateTimeImmutable
    {
        $sql = "SELECT net.point_sale_base.get_period_for_reports(0) as current_period FROM dual";

        $iterator = $this->conn->query($sql);

        $raw = iterator_to_array($iterator);
        $currentPeriod = isset($raw[0]['current_period']) ? new DateTimeImmutable($raw[0]['current_period']) : null;
        if (is_null($currentPeriod)) {
            throw new EntityNotFoundDatabaseException('не удалось вычислить текущий период');
        }

        return $currentPeriod->modify('first day of this month');
    }

    /** @return Enumerable<int, ExcelLoanRepresentation> */
    public function getLoansInExcelRepresentation(DateTimeImmutable $start, DateTimeImmutable $end): Enumerable
    {
        $sql = "SELECT
                  epc.id 
                , e.name employee_name
                , e.contract employee_contract
                , c.name employee_country
                , epc.months
                , epc.month_payment
                , g.contract guarantor_contract
                , g.name guarantor_name
                , epc.summ
                FROM net.employee_point_credits epc
                JOIN net.employee e ON e.id = epc.employee_id 
                JOIN net.country c ON c.id = e.country
                LEFT JOIN net.employee g ON g.id = epc.guarantor
                
                WHERE epc.start_date BETWEEN :start_date AND :end_date";

        $raw = $this->conn->query(
            $sql,
            [
                'start_date' => $start,
                'end_date'   => $end,
            ],
            [
                'start_date' => ParamType::DATE,
                'end_date'   => ParamType::DATE,
            ]);

        return $this->customDenormalizeToCollection($raw, ExcelLoanRepresentation::class);
    }
}
