<?php

declare(strict_types=1);

namespace App\Domain\OperationalEfficiency\DDMRP\UseCase;

use App\Domain\OperationalEfficiency\DDMRP\DTO\SetDdmrpParametersRequest;
use App\Domain\OperationalEfficiency\DDMRP\Repository\CokQueryRepository;
use App\Domain\OperationalEfficiency\DDMRP\Repository\DdmrpParametersCommandRepository;

class SetDdmrpParametersUseCase
{
    public function __construct(
        private CokQueryRepository $read,
        private DdmrpParametersCommandRepository $write
    ) {
    }

    public function setParameters(SetDdmrpParametersRequest $request): bool
    {
        $cok = $this->read->getCokByContract($request->contract);
        $cok->updateDdmrpParameters($request->ddmrpParameters);

        $ddmrpParameters = $cok->getDdmrpParameters();
        if ($ddmrpParameters->isRecordExists()) {
            $this->write->update($ddmrpParameters);
        } else {
            $this->write->create($ddmrpParameters);
        }

        return true;
    }
}
