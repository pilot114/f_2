<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\Repository;

use App\Domain\Finance\Kpi\Entity\Kpi;
use App\Domain\Finance\Kpi\Entity\KpiMetricHistory;
use Database\Connection\ParamMode;
use Database\Connection\ParamType;
use Database\ORM\CommandRepository;

/**
 * @extends CommandRepository<Kpi>
 */
class KpiCommandRepository extends CommandRepository
{
    protected string $entityName = Kpi::class;

    public function updateKpi(Kpi $kpi): Kpi
    {
        $raw = $this->normalize($kpi);
        $this->conn->procedure('tehno.pkpi.kpi_accrue_edit', [
            'pId'             => $raw['id'],
            'pKPI_value'      => $raw['kpi_value'],
            'pKPI_value_calc' => $raw['kpi_value_calc'] ?? null,
        ]);
        return $kpi;
    }

    public function updateMetricKpi(KpiMetricHistory $metric): KpiMetricHistory
    {
        $raw = $this->normalize($metric);
        $this->conn->procedure('tehno.pkpi.kpi_accrue_metric_edit', [
            'pMetric_id'     => $raw['id'],
            'pMetric_plan'   => $raw['plan_value'],
            'pMetric_fact'   => $raw['factual_value'],
            'pMetric_weight' => $raw['weight'],
        ]);
        return $metric;
    }

    // вызывается после того, как данные были отправлены по почте
    public function sendToTreasury(array $finEmpIds = []): bool
    {
        $this->conn->procedure('tehno.pkpi.kpi_accrue_set_sended_mass', [
            'pUserList' => $finEmpIds === [] ? null : implode(';', $finEmpIds),
        ], [
            'pUserList' => [ParamMode::IN, ParamType::STRING],
        ]);
        return true;
    }

    // выставляет всем 100 и очищает признак отправления
    public function autoComplete(array $finEmpIds = []): bool
    {
        $this->conn->procedure('tehno.pkpi.kpi_accrue_autofill_mass', [
            'pUserList' => $finEmpIds === [] ? null : implode(';', $finEmpIds),
        ], [
            'pUserList' => [ParamMode::IN, ParamType::STRING],
        ]);
        return true;
    }
}
