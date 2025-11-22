<?php

declare(strict_types=1);

use App\Domain\Finance\Kpi\Entity\Kpi;
use App\Domain\Finance\Kpi\Enum\KpiType;
use App\Domain\Finance\Kpi\Repository\KpiQueryRepository;
use App\Domain\Finance\Kpi\UseCase\GetListUseCase;
use Illuminate\Support\Collection;

it('get list kpi', function (): void {
    $repository = Mockery::mock(KpiQueryRepository::class);
    $useCase = new GetListUseCase($repository);

    $empId = 123;
    $q = null;

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

    $repository->shouldReceive('getList')
        ->with($empId, false, $q)
        ->andReturn($kpiCollection);

    $repository->shouldReceive('lastDateSend');

    ##########################################
    $result = $useCase->getList($empId);
    ##########################################

    expect($result[0])->toBeInstanceOf(Collection::class)
        ->and($result[0])->toHaveCount(2);
    ;
});
