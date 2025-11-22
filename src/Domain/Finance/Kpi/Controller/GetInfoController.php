<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\Controller;

use App\Common\Attribute\CpAction;
use App\Common\Attribute\RpcMethod;
use App\Common\Attribute\RpcParam;
use App\Common\Helper\EnumerableWithTotal;
use App\Domain\Finance\Kpi\DTO\InfoResponse;
use App\Domain\Finance\Kpi\Entity\KpiDepartment;
use App\Domain\Finance\Kpi\UseCase\GetBossesUseCase;
use App\Domain\Finance\Kpi\UseCase\GetListUseCase;
use App\Domain\Portal\Security\Entity\SecurityUser;
use App\Domain\Portal\Security\Repository\SecurityQueryRepository;
use Illuminate\Support\Enumerable;

class GetInfoController
{
    public function __construct(
        private GetListUseCase          $kpiUseCase,
        private GetBossesUseCase        $bossKpiUseCase,
        private SecurityUser            $currentUser,
        private SecurityQueryRepository $secRepo,
    ) {
    }

    #[RpcMethod(
        'finance.kpi.getInfo',
        'Общая информация по KPI',
        examples: [
            [
                'params' => [
                    'q'        => 'Иванов',
                    'onlyBoss' => false,
                ],
            ],
        ],
    )]
    #[CpAction('accured_kpi.accured_kpi_departmentboss')]
    public function __invoke(
        #[RpcParam('Поиск по имени пользователя')]
        ?string $q,
        #[RpcParam('Показывать только руководителей')]
        bool $onlyBoss = false,
    ): InfoResponse {
        /** @var Enumerable<int, KpiDepartment> $deps */
        $deps = $this->kpiUseCase->getList($this->currentUser->id, $q, $onlyBoss)[0];

        $kpis = [];
        foreach ($deps as $dep) {
            foreach ($dep->getKpi() as $kpi) {
                $kpis[] = $kpi;
            }
        }

        $isKpiSuperBoss = $this->secRepo->hasCpAction($this->currentUser->id, 'accured_kpi.accured_kpi_superboss');
        if ($isKpiSuperBoss) {
            foreach ($this->bossKpiUseCase->getBosses($q) as $boss) {
                foreach ($boss->getKpi() as $kpi) {
                    $kpis[] = $kpi;
                }
            }
        }

        return InfoResponse::build(EnumerableWithTotal::build($kpis));
    }
}
