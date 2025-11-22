<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\Controller;

use App\Common\Attribute\CpAction;
use App\Common\Attribute\RpcMethod;
use App\Common\DTO\FindResponse;
use App\Domain\Finance\Kpi\DTO\EmployeeSearchResponse;
use App\Domain\Finance\Kpi\DTO\SearchEmployeeRequest;
use App\Domain\Finance\Kpi\UseCase\SearchEmployeeUseCase;
use App\Domain\Portal\Security\Entity\SecurityUser;

class SearchEmployeeController
{
    public function __construct(
        private SearchEmployeeUseCase $useCase,
        private SecurityUser $currentUser,
    ) {
    }

    /**
     * @return FindResponse<EmployeeSearchResponse>
     */
    #[RpcMethod(
        'finance.kpi.searchEmployee',
        'Поиск сотрудника в системе начисления KPI',
        examples: [
            [
                'summary' => 'Поиск по ФИО сотрудника',
                'params'  => [
                    'search' => 'Иванов',
                ],
            ],
            [
                'summary' => 'Поиск для замещающего пользователя',
                'params'  => [
                    'search' => 'Петров',
                    'userId' => 12345,
                ],
            ],
        ],
        isAutomapped: true
    )]
    #[CpAction('accured_kpi.accured_kpi_admin or accured_kpi.accured_kpi_departmentboss')]
    public function __invoke(SearchEmployeeRequest $request): FindResponse
    {
        $employees = $this->useCase->searchEmployee($this->currentUser->id, $request);
        return new FindResponse($employees);
    }
}
