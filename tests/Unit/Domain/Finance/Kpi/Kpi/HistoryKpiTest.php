<?php

declare(strict_types=1);

use App\Domain\Finance\Kpi\Entity\Kpi;
use App\Domain\Finance\Kpi\Enum\KpiType;
use App\Domain\Finance\Kpi\Repository\KpiQueryRepository;
use App\Domain\Finance\Kpi\Repository\KpiResponsibleQueryRepository;
use App\Domain\Finance\Kpi\UseCase\GetHistoryUseCase;
use Illuminate\Support\Collection;

it('get history kpi', function (): void {
    $repository = Mockery::mock(KpiQueryRepository::class);
    $repository2 = Mockery::mock(KpiResponsibleQueryRepository::class);
    $useCase = new GetHistoryUseCase($repository, $repository2);

    $empId = 123;
    $kpiCollection = collect([
        new Kpi(
            id: 1,
            billingMonth: new DateTimeImmutable('01.01.2020'),
            type: KpiType::MONTHLY,
            value: 10,
            isSent: true
        ),
        new Kpi(
            id: 2,
            billingMonth: new DateTimeImmutable('01.01.2020'),
            type: KpiType::BIMONTHLY,
            value: 50,
            isSent: true
        ),
    ]);

    $repository->shouldReceive('getHistory')
        ->with($empId)
        ->andReturn($kpiCollection);
    $repository2->shouldReceive('getActualResponsible')
        ->once();

    ##########################################
    $result = $useCase->getHistory($empId);
    ##########################################

    expect($result[0])->toBeInstanceOf(Collection::class)
        ->and($result[0])->toHaveCount(2)
    ;
});
