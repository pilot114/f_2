<?php

declare(strict_types=1);

namespace App\Domain\OperationalEfficiency\DDMRP\UseCase;

use App\Domain\OperationalEfficiency\DDMRP\DTO\GetCokListRequest;
use App\Domain\OperationalEfficiency\DDMRP\Entity\Cok;
use App\Domain\OperationalEfficiency\DDMRP\Repository\CokQueryRepository;
use Illuminate\Support\Enumerable;

class GetCokListUseCase
{
    public function __construct(
        private CokQueryRepository $cokQueryRepository
    ) {
    }

    /** @return Enumerable<int, Cok> */
    public function getCokList(GetCokListRequest $request): Enumerable
    {
        return $this->cokQueryRepository->getCokList($request);
    }
}
