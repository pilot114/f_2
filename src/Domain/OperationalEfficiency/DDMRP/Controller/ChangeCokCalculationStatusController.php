<?php

declare(strict_types=1);

namespace App\Domain\OperationalEfficiency\DDMRP\Controller;

use App\Common\Attribute\CpMenu;
use App\Common\Attribute\RpcMethod;
use App\Common\Attribute\RpcParam;
use App\Domain\OperationalEfficiency\DDMRP\Enum\CalculationStatus;
use App\Domain\OperationalEfficiency\DDMRP\UseCase\ChangeCokCalculationStatusUseCase;

class ChangeCokCalculationStatusController
{
    public function __construct(
        private ChangeCokCalculationStatusUseCase $useCase
    ) {
    }

    #[RpcMethod(
        name: 'operationalEfficiency.ddmrp.changeCokCalculationStatus',
        summary: 'изменить статус для расчёта DDMRP',
        examples: [
            [
                'summary' => 'изменить статус для расчёта DDMRP',
                'params'  => [
                    'status'   => 1,
                    'contract' => 'C30060',
                ],
            ],
        ],
    )]
    #[CpMenu('ddmrp_admin')]
    public function __invoke(
        #[RpcParam('статус')] CalculationStatus $status,
        #[RpcParam('контракт')] string $contract
    ): bool {
        return $this->useCase->changeStatus($status, $contract);
    }
}
