<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\UseCase;

use App\Domain\Finance\Kpi\DTO\CreateKpiMetricDepartmentRequest;
use App\Domain\Finance\Kpi\DTO\CreateKpiMetricRequest;
use App\Domain\Finance\Kpi\DTO\UpdateKpiMetricRequest;
use App\Domain\Finance\Kpi\Entity\CpDepartment;
use App\Domain\Finance\Kpi\Entity\KpiMetric;
use App\Domain\Finance\Kpi\Entity\KpiMetricGroup;
use App\Domain\Finance\Kpi\Entity\KpiMetricType;
use App\Domain\Finance\Kpi\Entity\Post;
use App\Domain\Finance\Kpi\Enum\KpiCalculationType;
use App\Domain\Finance\Kpi\Enum\KpiType;
use App\Domain\Finance\Kpi\Enum\UnitType;
use App\Domain\Finance\Kpi\Repository\KpiMetricCommandRepository;
use App\Domain\Finance\Kpi\Repository\KpiMetricQueryRepository;
use Database\Connection\TransactionInterface;
use Database\ORM\Attribute\Loader;
use Database\ORM\QueryRepositoryInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class WriteMetricUseCase
{
    public function __construct(
        private KpiMetricCommandRepository $writeKpiMetricRepo,
        private KpiMetricQueryRepository   $readKpiMetricRepo,

        /** @var QueryRepositoryInterface<KpiMetricGroup> */
        private QueryRepositoryInterface   $readMetricGroup,
        /** @var QueryRepositoryInterface<KpiMetricType> */
        private QueryRepositoryInterface   $readMetricType,
        /** @var QueryRepositoryInterface<Post> */
        private QueryRepositoryInterface   $readPost,
        /** @var QueryRepositoryInterface<CpDepartment> */
        private QueryRepositoryInterface   $readDepartment,
        private TransactionInterface       $transaction,
    ) {
    }

    public function createMetric(CreateKpiMetricRequest $dto): KpiMetric
    {
        $this->transaction->beginTransaction();

        $group = $this->readMetricGroup->findOrFail($dto->groupId, 'Не найдена группа метрик');
        $metricType = $this->readMetricType->findOrFail($dto->metricTypeId, 'Не найден тип метрики');

        $metric = new KpiMetric(
            id: Loader::ID_FOR_INSERT,
            name: $dto->name,
            kpiType: $dto->kpiType,
            calculationType: $dto->calculationType,
            calculationTypeDescription: $dto->calculationTypeDescription,
            unitType: $dto->unitType,
            group: $group,
            type: $metricType,
        );
        $metric = $this->writeKpiMetricRepo->createMetric($metric);
        $this->linkMetricDepartments($dto->metricDepartments, $metric);

        $this->transaction->commit();

        return $metric;
    }

    /**
     * @param array<CreateKpiMetricDepartmentRequest> $metricDepartments
     */
    protected function linkMetricDepartments(array $metricDepartments, KpiMetric $metric): void
    {
        foreach ($metricDepartments as $metricDepartment) {
            $post = $this->readPost->findOrFail($metricDepartment->postId, 'Не найдена должность');
            $department = $this->readDepartment->findOrFail($metricDepartment->departmentId, 'Не найден департамент');

            $this->writeKpiMetricRepo->createLinkToDepartmentForMetric(
                $metric,
                $department,
                $post,
            );
        }
    }

    protected function unlinkMetricDepartments(KpiMetric $originalMetric): void
    {
        $departments = $originalMetric->toArray()['departments'] ?? [];

        foreach ($departments as $department) {
            $this->writeKpiMetricRepo->deleteLinkToDepartmentForMetric(
                $originalMetric,
                $department['departmentId'],
                $department['postId'],
            );
        }
    }

    // TODO: код стандартный, нужно более общее решение
    // TODO: для уcтановки NULL нужно специальное значение
    // TODO: явные сеттеры стоит заменить на установку через рефлексию, чтобы иметь более закрытый контракт
    public function updateMetric(UpdateKpiMetricRequest $dto): KpiMetric
    {
        $this->transaction->beginTransaction();

        $originalMetric = $this->readKpiMetricRepo->getMetric($dto->id);
        if (!$originalMetric instanceof KpiMetric) {
            throw new NotFoundHttpException("Не найдена метрика с id = {$dto->id}");
        }

        if ($dto->groupId !== null) {
            $group = $this->readMetricGroup->findOrFail($dto->groupId, 'Не найдена группа метрик');
            $originalMetric->setGroup($group);
        }
        if ($dto->metricTypeId !== null) {
            $type = $this->readMetricType->findOrFail($dto->metricTypeId, 'Не найден тип метрики');
            $originalMetric->setType($type);
        }

        if ($dto->name !== null) {
            $originalMetric->setName($dto->name);
        }
        if ($dto->kpiType instanceof KpiType) {
            $originalMetric->setKpiType($dto->kpiType);
        }
        if ($dto->calculationType instanceof KpiCalculationType) {
            $originalMetric->setCalculationType($dto->calculationType);
        }
        if ($dto->calculationTypeDescription !== null) {
            $originalMetric->setCalculationTypeDescription($dto->calculationTypeDescription);
        }
        if ($dto->unitType instanceof UnitType) {
            $originalMetric->setUnitType($dto->unitType);
        }
        if ($dto->isActive !== null) {
            $originalMetric->setIsActive($dto->isActive);
        }
        $originalMetric = $this->writeKpiMetricRepo->updateMetric($originalMetric);

        if ($dto->metricDepartments !== []) {
            $this->unlinkMetricDepartments($originalMetric);
            $this->linkMetricDepartments($dto->metricDepartments, $originalMetric);
        }

        $this->transaction->commit();
        return $originalMetric;
    }
}
