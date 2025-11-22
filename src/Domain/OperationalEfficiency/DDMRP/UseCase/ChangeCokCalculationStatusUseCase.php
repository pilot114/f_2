<?php

declare(strict_types=1);

namespace App\Domain\OperationalEfficiency\DDMRP\UseCase;

use App\Domain\OperationalEfficiency\DDMRP\Enum\CalculationStatus;
use App\Domain\OperationalEfficiency\DDMRP\Repository\CokCommandRepository;
use App\Domain\OperationalEfficiency\DDMRP\Repository\CokQueryRepository;

class ChangeCokCalculationStatusUseCase
{
    public function __construct(
        private CokCommandRepository $write,
        private CokQueryRepository $read
    ) {
    }

    public function changeStatus(CalculationStatus $calculationStatus, string $contract): bool
    {
        $cok = $this->read->getCokByContract($contract);
        $cok->changeCalculationStatus($calculationStatus);
        $this->write->update($cok);

        return true;
    }
}
