<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\UseCase;

use App\Domain\Finance\Kpi\DTO\UpdateKpiRequest;
use App\Domain\Finance\Kpi\Entity\FinEmployee;
use App\Domain\Finance\Kpi\Entity\Kpi;
use App\Domain\Finance\Kpi\Repository\KpiCommandRepository;
use App\Domain\Finance\Kpi\Repository\KpiMetricHistoryQueryRepository;
use App\Domain\Finance\Kpi\Repository\KpiQueryRepository;
use Database\Connection\TransactionInterface;
use Database\ORM\QueryRepositoryInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MultiUpdateUseCase
{
    public function __construct(
        private TransactionInterface       $transaction,
        private KpiCommandRepository       $write,
        private KpiQueryRepository         $read,
        /** @var QueryRepositoryInterface<FinEmployee> */
        private QueryRepositoryInterface   $finEmpRepo,
        private KpiMetricHistoryQueryRepository $kpiMetricHistoryQuery,
    ) {
    }

    /**
     * @param array<UpdateKpiRequest> $kpi
     * @return  array<Kpi>
     */
    public function multipleUpdateKpi(array $kpi): array
    {
        $this->transaction->beginTransaction();

        $result = [];
        foreach ($kpi as $item) {
            $result[] = $this->updateKpi($item);
        }

        $this->transaction->commit();

        return $result;
    }

    private function updateKpi(UpdateKpiRequest $kpiRequest): Kpi
    {
        $finEmp = $this->finEmpRepo->findOneBy([
            'cp_id' => $kpiRequest->empId,
        ]);
        if ($finEmp === null) {
            throw new NotFoundHttpException("Не найдена финансовая информация по сотруднику с id $kpiRequest->empId");
        }
        $kpi = $this->read->findOrFail($kpiRequest->id, 'Не найден KPI');
        $kpi->setValue($kpiRequest->value);
        $kpi->setValueCalculated($kpiRequest->valueCalculated);

        $entityMetrics = [];
        foreach ($kpiRequest->metrics as $metric) {
            $entityMetric = $this->kpiMetricHistoryQuery->findOrFail($metric->id, 'Не найдена запись по истории метрики');
            $entityMetric->setData($metric->plan, $metric->factual, $metric->weight);
            $this->write->updateMetricKpi($entityMetric);
            $entityMetrics[] = $entityMetric;
        }
        $kpi->setMetricHistory($entityMetrics);

        return $this->write->updateKpi($kpi);
    }
}
