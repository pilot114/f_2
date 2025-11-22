<?php

declare(strict_types=1);

namespace App\Domain\OperationalEfficiency\DDMRP\Controller;

use App\Common\Attribute\CpMenu;
use App\Common\Attribute\RpcMethod;
use App\Domain\OperationalEfficiency\DDMRP\DTO\SetDdmrpParametersRequest;
use App\Domain\OperationalEfficiency\DDMRP\UseCase\SetDdmrpParametersUseCase;

class SetDdmrpParametersController
{
    public function __construct(
        private SetDdmrpParametersUseCase $useCase
    ) {
    }

    #[RpcMethod(
        name: 'operationalEfficiency.ddmrp.setDdmrpParameters',
        summary: 'установить параметры DDMRP для ЦОК',
        examples: [
            [
                'summary' => 'установить параметры DDMRP',
                'params'  => [
                    'ddmrpParameters' => [
                        'dvf'               => 1.1,
                        'dltf'              => 2,
                        'dlt'               => 5,
                        'reOrderPoint'      => 10,
                        'expirationPercent' => 50,
                        'moq'               => 100,
                        'slt'               => 95,
                    ],
                    'contract' => 'C30070',
                ],
            ],
        ],
        isAutomapped: true
    )]
    #[CpMenu('ddmrp_admin')]
    public function __invoke(SetDdmrpParametersRequest $request): bool
    {
        return $this->useCase->setParameters($request);
    }
}
