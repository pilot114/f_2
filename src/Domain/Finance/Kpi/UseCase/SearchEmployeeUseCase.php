<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\UseCase;

use App\Domain\Finance\Kpi\DTO\EmployeeSearchResponse;
use App\Domain\Finance\Kpi\DTO\SearchEmployeeRequest;
use App\Domain\Finance\Kpi\Repository\KpiEmployeeSearchQueryRepository;
use App\Domain\Portal\Security\Repository\SecurityQueryRepository;

class SearchEmployeeUseCase
{
    private const ADMIN_ACTION = 'accured_kpi.accured_kpi_admin';

    public function __construct(
        private KpiEmployeeSearchQueryRepository $employeeSearchRepo,
        private SecurityQueryRepository $securityRepo,
    ) {
    }

    /**
     * Поиск сотрудников
     * @return EmployeeSearchResponse[]
     */
    public function searchEmployee(int $currentUserId, SearchEmployeeRequest $request): array
    {
        // Определяем, является ли пользователь админом
        $isAdmin = $this->securityRepo->hasCpAction($currentUserId, self::ADMIN_ACTION);

        // Выполняем поиск в зависимости от прав
        if ($isAdmin) {
            return $this->employeeSearchRepo->searchEmployeeForAdmin($request->search);
        }

        // Используем userId из запроса или текущего пользователя
        $userId = $request->userId ?? $currentUserId;
        return $this->employeeSearchRepo->searchEmployeeForDepartmentBoss($userId, $request->search);
    }
}
