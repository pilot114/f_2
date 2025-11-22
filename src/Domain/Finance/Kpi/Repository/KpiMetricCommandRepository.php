<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\Repository;

use App\Domain\Finance\Kpi\Entity\CpDepartment;
use App\Domain\Finance\Kpi\Entity\KpiMetric;
use App\Domain\Finance\Kpi\Entity\Post;
use Database\Connection\ParamMode;
use Database\Connection\ParamType;
use Database\ORM\CommandRepository;

/**
 * @extends CommandRepository<KpiMetric>
 */
class KpiMetricCommandRepository extends CommandRepository
{
    protected string $entityName = KpiMetric::class;

    public function createMetric(KpiMetric $metric): KpiMetric
    {
        $raw = $this->normalize($metric);
        $result = $this->conn->procedure('tehno.pkpi.kpi_metric_add', [
            'pMetric_group_id'     => $raw['group']['id'],
            'pName'                => $raw['name'],
            'pMetric_type_id'      => $raw['type']['id'],
            'pMetric_unit_type_id' => $raw['unit_type_id'],
            'pKPI_type_id'         => $raw['kpi_type'],
            'pDescription'         => $raw['calculation_description'],
            'pCalculation_type'    => $raw['calculation_type'],
            'oMetric_id'           => null,
        ], [
            'oMetric_id' => [ParamMode::OUT, ParamType::INTEGER],
        ]);
        $raw['id'] = $result['oMetric_id'];
        return $this->denormalize($raw);
    }

    public function createLinkToDepartmentForMetric(KpiMetric $metric, CpDepartment $department, Post $post): void
    {
        $this->conn->insert('tehno.kpi_metric_department', [
            'kpi_metric_id'     => $metric->getId(),
            'cp_departament_id' => $department->getId(),
            'cp_response_id'    => $post->getId(),
        ]);
        $metric->addMetricDepartment($department, $post);
    }

    public function deleteLinkToDepartmentForMetric(KpiMetric $originalMetric, int $departmentId, int $postId): void
    {
        $this->conn->delete('tehno.kpi_metric_department', [
            'kpi_metric_id'     => $originalMetric->getId(),
            'cp_departament_id' => $departmentId,
            'cp_response_id'    => $postId,
        ]);
        $originalMetric->removeMetricDepartment($departmentId, $postId);
    }

    public function updateMetric(KpiMetric $metric): KpiMetric
    {
        $raw = $this->normalize($metric);
        $this->conn->procedure('tehno.pkpi.kpi_metric_edit', [
            'pMetric_id'           => $raw['id'],
            'pMetric_group_id'     => $raw['group']['id'],
            'pName'                => $raw['name'],
            'pMetric_type_id'      => $raw['type']['id'],
            'pMetric_unit_type_id' => $raw['unit_type_id'],
            'pKPI_type_id'         => $raw['kpi_type'],
            'pDescription'         => $raw['calculation_description'],
            'pIs_active'           => (int) $raw['is_active'],
        ], [
            'pIs_active' => [ParamMode::IN, ParamType::INTEGER],
        ]);
        return $metric;
    }
}
