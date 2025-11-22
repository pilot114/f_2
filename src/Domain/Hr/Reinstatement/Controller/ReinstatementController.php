<?php

declare(strict_types=1);

namespace App\Domain\Hr\Reinstatement\Controller;

use App\Common\Attribute\RpcMethod;
use App\Common\Attribute\RpcParam;
use App\Common\DTO\FindResponse;
use App\Domain\Hr\Reinstatement\DTO\EmployeeForReinstatement;
use App\Domain\Hr\Reinstatement\Entity\Employee;
use App\Domain\Hr\Reinstatement\UseCase\ReinstatementUseCase;

class ReinstatementController
{
    public function __construct(
        private ReinstatementUseCase $reinstatementUseCase,
    ) {
    }
    /**
     * @return FindResponse<Employee>
     */
    #[RpcMethod(
        'hr.reinstatement.findEmployeeByNamePart',
        'Поиск сотрудника по частичному совпадению имени',
        examples: [
            [
                'summary' => 'Поиск сотрудника то ли Ивлеева то ли Ивлева ',
                'params'  => [
                    'query' => 'ивле',
                ],
            ],
        ],
    )]
    public function findEmployeeByNamePart(#[RpcParam('Часть имени для поиска')] string $query): FindResponse
    {
        $data = $this->reinstatementUseCase->getEmployeeByNamePart($query);
        return new FindResponse(EmployeeForReinstatement::arrayFromEntity($data));
    }

    #[RpcMethod(
        'hr.reinstatement.reinstateEmployee',
        'Восстановить сотрудника по id',
        examples: [
            [
                'summary' => 'Восстановить сотрудника 1334',
                'params'  => [
                    'id' => 1334,
                ],
            ],
        ],
    )]
    public function reinstateEmployee(#[RpcParam('id сотрудника')] int $id): bool
    {
        return $this->reinstatementUseCase->reinstateEmployee($id);
    }

}
