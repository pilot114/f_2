<?php

declare(strict_types=1);

use App\Domain\Finance\Kpi\DTO\UpdateKpiRequest;
use App\Domain\Finance\Kpi\DTO\UpdateMetricKpiRequest;
use App\Domain\Finance\Kpi\Entity\FinEmployee;
use App\Domain\Finance\Kpi\Entity\Kpi;
use App\Domain\Finance\Kpi\Entity\KpiMetricHistory;
use App\Domain\Finance\Kpi\Repository\KpiCommandRepository;
use App\Domain\Finance\Kpi\Repository\KpiMetricHistoryQueryRepository;
use App\Domain\Finance\Kpi\Repository\KpiQueryRepository;
use App\Domain\Finance\Kpi\UseCase\MultiUpdateUseCase;
use Database\Connection\TransactionInterface;
use Database\EntityNotFoundDatabaseException;
use Database\ORM\QueryRepositoryInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

beforeEach(function (): void {
    $this->transactionMock = mock(TransactionInterface::class);
    $this->writeMock = mock(KpiCommandRepository::class);
    $this->readMock = mock(KpiQueryRepository::class);
    $this->finEmpRepoMock = mock(QueryRepositoryInterface::class);
    $this->kpiMetricHistoryQueryMock = mock(KpiMetricHistoryQueryRepository::class);

    $this->useCase = new MultiUpdateUseCase(
        $this->transactionMock,
        $this->writeMock,
        $this->readMock,
        $this->finEmpRepoMock,
        $this->kpiMetricHistoryQueryMock
    );
});

it('should successfully update multiple KPIs', function (): void {
    $empId = 1;
    $kpiId = 100;
    $metricId = 200;

    $finEmployeeMock = mock(FinEmployee::class);
    $kpiMock = mock(Kpi::class);
    $metricMock = mock(KpiMetricHistory::class);

    $this->finEmpRepoMock->shouldReceive('findOneBy')
        ->with([
            'cp_id' => $empId,
        ])
        ->andReturn($finEmployeeMock);
    $this->readMock->shouldReceive('findOrFail')
        ->with($kpiId, 'Не найден KPI')
        ->andReturn($kpiMock);
    $this->kpiMetricHistoryQueryMock->shouldReceive('findOrFail')
        ->with($metricId, 'Не найдена запись по истории метрики')
        ->andReturn($metricMock);

    $this->transactionMock->shouldReceive('beginTransaction')->once();
    $this->transactionMock->shouldReceive('commit')->once();

    $metricMock->shouldReceive('setData')->with(10, 20, 30)->once();

    $this->writeMock->shouldReceive('updateMetricKpi')->with($metricMock)->once();
    $kpiMock->shouldReceive('setValue')->with(100)->once();
    $kpiMock->shouldReceive('setValueCalculated')->with(100)->once();
    $kpiMock->shouldReceive('setMetricHistory')->with([$metricMock])->once();
    $this->writeMock->shouldReceive('updateKpi')->with($kpiMock)->andReturn($kpiMock)->once();

    $updateRequest = new UpdateKpiRequest($kpiId, $empId, 100, 100, [
        new UpdateMetricKpiRequest(id: $metricId, factual: 20, plan: 10, weight: 30),
    ]);

    ##########################################
    $result = $this->useCase->multipleUpdateKpi([$updateRequest]);
    ##########################################

    expect($result)->toBeArray()
        ->and($result)->toHaveCount(1)
        ->and($result[0])->toBeInstanceOf(Kpi::class);
});

it('should throw NotFoundHttpException if employee not found', function (): void {
    $empId = 1;
    $kpiId = 100;

    $this->finEmpRepoMock->shouldReceive('findOneBy')
        ->with([
            'cp_id' => $empId,
        ])
        ->andReturn(null);

    $updateRequest = new UpdateKpiRequest($kpiId, $empId, 100, 100, []);

    $this->transactionMock->shouldReceive('beginTransaction')->once();

    $this->expectException(NotFoundHttpException::class);
    $this->useCase->multipleUpdateKpi([$updateRequest]);
});

it('should throw EntityNotFoundDatabaseException if KPI not found', function (): void {
    $empId = 1;
    $kpiId = 100;

    $this->finEmpRepoMock->shouldReceive('findOneBy')
        ->with([
            'cp_id' => $empId,
        ])
        ->andReturn(mock(FinEmployee::class));

    $this->readMock
        ->shouldReceive('findOrFail')->with($kpiId, 'Не найден KPI')
        ->andThrow(EntityNotFoundDatabaseException::class)
    ;

    $updateRequest = new UpdateKpiRequest($kpiId, $empId, 100, 100, []);

    $this->transactionMock->shouldReceive('beginTransaction')->once();

    $this->expectException(EntityNotFoundDatabaseException::class);
    $this->useCase->multipleUpdateKpi([$updateRequest]);
});

it('should throw EntityNotFoundDatabaseException if metric not found', function (): void {
    $empId = 1;
    $kpiId = 100;
    $metricId = 200;

    $this->finEmpRepoMock->shouldReceive('findOneBy')
        ->with([
            'cp_id' => $empId,
        ])
        ->andReturn(
            mock(FinEmployee::class)
        );

    $this->readMock->shouldReceive('findOrFail')
        ->with($kpiId, 'Не найден KPI')
        ->andThrow(EntityNotFoundDatabaseException::class)
    ;

    $this->kpiMetricHistoryQueryMock->shouldReceive('findOrFail')
        ->with($metricId, 'Не найдена запись по истории метрики')
        ->andThrow(EntityNotFoundDatabaseException::class)
    ;

    $this->transactionMock->shouldReceive('beginTransaction')->once();

    $this->expectException(EntityNotFoundDatabaseException::class);

    $this->useCase->multipleUpdateKpi([
        new UpdateKpiRequest($kpiId, $empId, 100, 100, [
            new UpdateMetricKpiRequest(id: $metricId, factual: 20, plan: 10, weight: 30),
        ]),
    ]);
});
