<?php

declare(strict_types=1);

namespace App\Domain\Finance\SelfEmployed\Repository;

use App\Domain\Finance\SelfEmployed\Entity\ReportData;
use Database\Connection\ParamMode;
use Database\Connection\ParamType;
use Database\ORM\CommandRepository;
use DateTimeImmutable;

/**
 * @extends CommandRepository<ReportData>
 */
class SelfEmployedLimitRepository extends CommandRepository
{
    protected string $entityName = ReportData::class;

    public function getReport(DateTimeImmutable $dateFrom, DateTimeImmutable $dateTill): array
    {
        $data = $this->conn->procedure('net.pcursors.exceeding_limit_self_employed_rep', [
            'o_result'  => null,
            'i_dt_from' => $dateFrom,
            'i_dt_to'   => $dateTill,
        ], [
            'o_result'  => [ParamMode::OUT, ParamType::CURSOR],
            'i_dt_from' => [ParamMode::IN, ParamType::DATE],
            'i_dt_to'   => [ParamMode::IN, ParamType::DATE],
        ]);
        return $data['o_result'];
    }
}
