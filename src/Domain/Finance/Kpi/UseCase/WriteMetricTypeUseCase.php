<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\UseCase;

use App\Domain\Finance\Kpi\DTO\CreateKpiMetricTypeRequest;
use App\Domain\Finance\Kpi\DTO\CreateKpiRangeRequest;
use App\Domain\Finance\Kpi\DTO\UpdateKpiMetricTypeRequest;
use App\Domain\Finance\Kpi\DTO\UpdateKpiRangeRequest;
use App\Domain\Finance\Kpi\Entity\KpiMetricRange;
use App\Domain\Finance\Kpi\Entity\KpiMetricType;
use App\Domain\Finance\Kpi\Enum\PaymentPlanType;
use App\Domain\Finance\Kpi\Repository\KpiMetricTypeCommandRepository;
use App\Domain\Finance\Kpi\Repository\KpiRangesCommandRepository;
use App\Domain\Finance\Kpi\Repository\KpiRangesQueryRepository;
use Database\Connection\TransactionInterface;
use Database\ORM\Attribute\Loader;
use Database\ORM\QueryRepositoryInterface;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class WriteMetricTypeUseCase
{
    public function __construct(
        private KpiMetricTypeCommandRepository $writeMetricTypeRepo,
        /** @var QueryRepositoryInterface<KpiMetricType> */
        private QueryRepositoryInterface       $readMetricTypeRepo,
        private KpiRangesCommandRepository     $writeRangesRepo,
        private KpiRangesQueryRepository       $readRangesRepo,
        private TransactionInterface           $transaction,
    ) {
    }

    public function createMetricType(CreateKpiMetricTypeRequest $metricType): KpiMetricType
    {
        $this->transaction->beginTransaction();

        // создаем метрику
        $createdMetricType = $this->writeMetricTypeRepo->createMetricType(new KpiMetricType(
            id: Loader::ID_FOR_INSERT,
            name: $metricType->name,
            planType: $metricType->planType,
            ranges: [],
            isActive: true
        ));

        // создаем диапазоны
        $ranges = [];
        if ($metricType->planType === PaymentPlanType::RANGES) {
            $ranges = $this->saveRanges($metricType->ranges, $createdMetricType->getId());
        }

        $this->transaction->commit();

        return $createdMetricType->setRanges($ranges);
    }

    public function updateMetricType(UpdateKpiMetricTypeRequest $metricType): KpiMetricType
    {
        $entity = $this->readMetricTypeRepo->findOrFail($metricType->id, 'Не найден тип метрики');

        $this->transaction->beginTransaction();

        // обновляем метрику
        if ($metricType->name !== null) {
            $entity->setName($metricType->name);
        }
        if ($metricType->planType instanceof PaymentPlanType) {
            $entity->setPlanType($metricType->planType);
        }
        if ($metricType->isActive !== null) {
            $entity->setIsActive($metricType->isActive);
        }

        $entity = $this->writeMetricTypeRepo->updateMetricType($entity);

        // пересоздаем диапазоны
        if ($metricType->planType === PaymentPlanType::RANGES) {
            $this->writeRangesRepo->deleteRangesByMetricTypeId($metricType->id);
            $ranges = $this->saveRanges($metricType->ranges, $metricType->id);
            $entity->setRanges($ranges);
        }

        $this->transaction->commit();
        return $entity;
    }

    /**
     * @param array<CreateKpiRangeRequest | UpdateKpiRangeRequest> $ranges
     * @return array<KpiMetricRange>
     */
    protected function saveRanges(array $ranges, int $metricTypeId): array
    {
        $entityRanges = [];
        foreach ($ranges as $range) {
            $existInRange = $this->readRangesRepo->existInRange($range->startPercent, $range->endPercent, $metricTypeId);
            if ($existInRange) {
                throw new ConflictHttpException('Диапазон пересекается с уже существующим диапазоном');
            }
            $entityRanges[] = $this->writeRangesRepo->addRange(new KpiMetricRange(
                id: Loader::ID_FOR_INSERT,
                startPercent: $range->startPercent,
                endPercent: $range->endPercent,
                kpiPercent: $range->kpiPercent,
                metricTypeId: $metricTypeId
            ));
        }
        return $entityRanges;
    }
}
