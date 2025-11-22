<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\Repository;

use App\Domain\Finance\Kpi\Entity\KpiMetric;
use App\Domain\Finance\Kpi\Entity\KpiMetricGroup;
use App\Domain\Finance\Kpi\Entity\KpiMetricType;
use Database\ORM\QueryRepository;
use Illuminate\Support\Enumerable;

/**
 * @extends QueryRepository<KpiMetric>
 */
class KpiMetricQueryRepository extends QueryRepository
{
    protected string $entityName = KpiMetric::class;

    protected string $baseSql = <<<SQL
            select
                ------------------------------------------ метрика
                m.id id,
                m.name name
            from tehno.kpi_metric m
        SQL;

    protected string $baseSqlExtended = <<<SQL
        select
            ------------------------------------------ метрика
            m.id                      id,
            m.name                    name,
            m.kpi_type_id             kpi_type,
            m.calculation_type        calculation_type,
            m.calculation_description calculation_description,
            m.kpi_metric_unit_type_id unit_type_id,
            ------------------------------------------ группа метрики
            mg.id group_id,
            mg.name group_name,
            ------------------------------------------ тип метрики
            mt.id type_id,
            mt.name type_name,
            mt.payment_plan_type type_payment_plan_type,
            ------------------------------------------ диапазоны типа метрики
            r.id          type_ranges_id,
            r.start_value type_ranges_start_value,
            r.end_value   type_ranges_end_value,
            r.kpi_percent type_ranges_kpi_percent,
            ------------------------------------------ Подразделения для метрики
            d.id     departments_id,
            d.name   departments_name,
            ------------------------------------------ Должности для метрики
            res.id   departments_posts_id,
            res.name departments_posts_name
        from tehno.kpi_metric m
        join tehno.kpi_metric_group mg      on mg.id = m.kpi_metric_group_id
        join tehno.kpi_metric_type mt       on mt.id = m.kpi_metric_type_id
        left join tehno.kpi_metric_type_ranges r on r.kpi_metric_type_id = mt.id
        join tehno.kpi_metric_department md on md.kpi_metric_id = m.id
        join test.cp_departament d          on d.id = md.cp_departament_id 
        join test.cp_response res           on res.id = md.cp_response_id
    SQL;

    protected string $baseSqlForTypes = <<<SQL
            select
                ------------------------------------------ тип метрики
                mt.id id,
                mt.name name
            from tehno.kpi_metric_type mt
        SQL;

    protected string $baseSqlForTypesExtended = <<<SQL
            select
                ------------------------------------------ тип метрики
                mt.id id,
                mt.name name,
                mt.payment_plan_type payment_plan_type,
                ------------------------------------------ диапазоны типа метрики
                r.id          ranges_id,
                r.start_value ranges_start_value,
                r.end_value   ranges_end_value,
                r.kpi_percent ranges_kpi_percent,
                ------------------------------------------ метрика
                m.id    metrics_id,
                m.name  metrics_name,
                m.kpi_type_id  metrics_kpi_type,
                m.calculation_type  metrics_calculation_type,
                m.calculation_description  metrics_calculation_description,
                m.kpi_metric_unit_type_id metrics_unit_type_id
            from tehno.kpi_metric_type mt
            left join tehno.kpi_metric_type_ranges r on r.kpi_metric_type_id = mt.id
            left join tehno.kpi_metric m on m.kpi_metric_type_id = mt.id and m.is_active = 1
        SQL;

    /**
     * @return Enumerable<int, KpiMetricGroup>
     */
    public function getMetricGroups(): Enumerable
    {
        $sql = <<<SQL
            select
                ------------------------------------------ группа метрики
                mg.id id,
                mg.name name
            from tehno.kpi_metric_group mg
            where mg.is_active = 1
        SQL;

        return $this->customDenormalizeToCollection(
            $this->conn->query($sql),
            KpiMetricGroup::class
        );
    }

    public function getMetrics(?string $q = null, bool $withDisabled = false): array
    {
        $params = [];
        $qWhere = '';
        if ($q !== null) {
            $params['q'] = $q;
            $qWhere = "and lower(m.name) like '%' || lower(:q) || '%'";
        }
        $onlyActive = $withDisabled ? '' : 'and m.is_active = 1';

        $sql = <<<SQL
            $this->baseSql
            where
                1=1
                $onlyActive
                $qWhere
        SQL;

        return iterator_to_array($this->conn->query($sql, $params));
    }

    /**
     * @return Enumerable<int, KpiMetric>
     */
    public function getMetricsExtended(
        ?int $groupId = null,
        ?int $metricTypeId = null,
        ?string $q = null,
        bool $withDisabled = false
    ): Enumerable {
        $params = [];
        $qWhere = '';
        if ($q !== null) {
            $params['q'] = $q;
            $qWhere = "and lower(m.name) like '%' || lower(:q) || '%'";
        }

        $groupWhere = '';
        if ($groupId !== null) {
            $params['groupId'] = $groupId;
            $groupWhere = 'and mg.id = :groupId';
        }

        $metricTypeWhere = '';
        if ($metricTypeId !== null) {
            $params['metricTypeId'] = $metricTypeId;
            $metricTypeWhere = 'and mt.id = :metricTypeId';
        }

        $onlyActive = $withDisabled ? '' : 'and m.is_active = 1';

        $sql = <<<SQL
            $this->baseSqlExtended
            where
                1=1
                $onlyActive
                $qWhere
                $metricTypeWhere
                $groupWhere
        SQL;

        return $this->query($sql, $params);
    }

    public function getMetric(int $id): ?KpiMetric
    {
        $sql = <<<SQL
            $this->baseSqlExtended
            where
                m.is_active = 1
                and m.id = :id
        SQL;

        return $this->query($sql, [
            'id' => $id,
        ])->first();
    }

    public function getMetricType(int $id): ?KpiMetricType
    {
        $sql = <<<SQL
            $this->baseSqlForTypesExtended
            where
                mt.is_active = 1
                and mt.id = :id
        SQL;

        $raw = $this->conn->query($sql, [
            'id' => $id,
        ]);
        return $this->customDenormalizeToCollection($raw, KpiMetricType::class)->first();
    }

    public function getMetricTypes(?string $q = null, bool $withDisabled = false): array
    {
        $params = [];

        $qWhere = '';
        if ($q !== null) {
            $params['q'] = $q;
            $qWhere = "and lower(mt.name) like '%' || lower(:q) || '%'";
        }

        $onlyActive = $withDisabled ? '' : 'and mt.is_active = 1';

        $sql = <<<SQL
            $this->baseSqlForTypes
            where
                1=1
                $onlyActive
                $qWhere
            order by mt.name
        SQL;

        return iterator_to_array($this->conn->query($sql, $params));
    }

    /**
     * @return Enumerable<int, KpiMetricType>
     */
    public function getMetricTypesExtends(?string $q = null, bool $withDisabled = false): Enumerable
    {
        $params = [];

        $qWhere = '';
        if ($q !== null) {
            $params['q'] = $q;
            $qWhere = "and lower(mt.name) like '%' || lower(:q) || '%'";
        }

        $onlyActive = $withDisabled ? '' : 'and mt.is_active = 1';

        $sql = <<<SQL
            $this->baseSqlForTypesExtended
            where
                1=1
                $onlyActive
                $qWhere
            order by mt.name, r.start_value, m.name
        SQL;

        $raw = $this->conn->query($sql, $params);
        return $this->customDenormalizeToCollection($raw, KpiMetricType::class);
    }
}
