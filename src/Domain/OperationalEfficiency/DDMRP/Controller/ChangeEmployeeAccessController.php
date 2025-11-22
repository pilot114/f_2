<?php

declare(strict_types=1);

namespace App\Domain\OperationalEfficiency\DDMRP\Controller;

use App\Common\Attribute\CpMenu;
use App\Common\Attribute\RpcMethod;
use App\Domain\OperationalEfficiency\DDMRP\DTO\ChangeEmployeeAccessRequest;
use App\Domain\OperationalEfficiency\DDMRP\UseCase\ChangeEmployeeAccessUseCase;

class ChangeEmployeeAccessController
{
    public function __construct(
        private ChangeEmployeeAccessUseCase $useCase
    ) {
    }

    #[RpcMethod(
        name: 'operationalEfficiency.ddmrp.changeEmployeeAccess',
        summary: 'изменить доступ сотрудника к заказам DDMRP',
        examples: [
            [
                'summary' => 'предоставить доступ сотруднику',
                'params'  => [
                    'contract'    => 'C30070',
                    'employeeId'  => 12345,
                    'grantAccess' => true,
                ],
            ],
        ],
        isAutomapped: true
    )]
    #[CpMenu('ddmrp_admin')]
    public function __invoke(ChangeEmployeeAccessRequest $request): bool
    {
        return $this->useCase->changeAccess($request);
    }
}
