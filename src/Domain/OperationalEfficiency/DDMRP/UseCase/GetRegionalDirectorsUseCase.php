<?php

declare(strict_types=1);

namespace App\Domain\OperationalEfficiency\DDMRP\UseCase;

use App\Domain\OperationalEfficiency\DDMRP\Entity\RegionalDirector;
use App\Domain\OperationalEfficiency\DDMRP\Repository\EmployeeQueryRepository;
use Illuminate\Support\Enumerable;

class GetRegionalDirectorsUseCase
{
    public function __construct(
        private EmployeeQueryRepository $repository
    ) {
    }

    /** @return Enumerable<int, RegionalDirector> */
    public function getRegionalDirectors(): Enumerable
    {
        return $this->repository->getRegionalDirectors();
    }
}
