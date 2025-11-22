<?php

declare(strict_types=1);

namespace App\Domain\Hr\MemoryPages\UseCase;

use App\Domain\Hr\MemoryPages\DTO\GetEmployeeListRequest;
use App\Domain\Hr\MemoryPages\Entity\Employee;
use App\Domain\Hr\MemoryPages\Repository\EmployeeQueryRepository;
use Illuminate\Support\Enumerable;

class GetEmployeeListUseCase
{
    public function __construct(
        private EmployeeQueryRepository $repository
    ) {
    }

    /** @return Enumerable<int, Employee> */
    public function getList(GetEmployeeListRequest $request): Enumerable
    {
        return $this->repository->getList($request);
    }
}
