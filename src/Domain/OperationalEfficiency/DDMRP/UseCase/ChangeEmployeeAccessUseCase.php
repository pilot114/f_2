<?php

declare(strict_types=1);

namespace App\Domain\OperationalEfficiency\DDMRP\UseCase;

use App\Domain\OperationalEfficiency\DDMRP\DTO\ChangeEmployeeAccessRequest;
use App\Domain\OperationalEfficiency\DDMRP\Entity\CokEmployee;
use App\Domain\OperationalEfficiency\DDMRP\Entity\DdmrpEmployeeAccess;
use App\Domain\OperationalEfficiency\DDMRP\Repository\CokQueryRepository;
use App\Domain\OperationalEfficiency\DDMRP\Repository\DdmrpEmployeeAccessCommandRepository;
use App\Domain\Portal\Security\Enum\AccessType;
use App\Domain\Portal\Security\Repository\SecurityCommandRepository;
use Database\Connection\TransactionInterface;
use Database\ORM\Attribute\Loader;

class ChangeEmployeeAccessUseCase
{
    public const DDMRP_ORDER_CP_MENU_ID = 5433;

    public function __construct(
        private CokQueryRepository                   $cokRead,
        private DdmrpEmployeeAccessCommandRepository $ddmrpAccessWrite,
        private SecurityCommandRepository            $cpMenuAccessWrite,
        private TransactionInterface                 $transaction
    ) {
    }

    public function changeAccess(ChangeEmployeeAccessRequest $request): bool
    {
        $cok = $this->cokRead->getCokByContract($request->contract);
        $cok->canChangeEmployeeAccess();

        $employee = $cok->getEmployee($request->employeeId);

        if ($request->grantAccess) {
            $this->grantAccess($employee);
        } else {
            $this->revokeAccess($employee);
        }

        return true;
    }

    private function grantAccess(CokEmployee $employee): void
    {
        $existingAccess = $employee->getAccessToDdmrp();

        if ($existingAccess instanceof DdmrpEmployeeAccess) {
            return;
        }

        $access = new DdmrpEmployeeAccess(
            id: Loader::ID_FOR_INSERT,
            contract: $employee->getCokContract(),
            cpEmpId: $employee->getId()
        );

        $this->transaction->beginTransaction();
        $this->ddmrpAccessWrite->create($access);
        foreach ([AccessType::READ, AccessType::WRITE] as $accessType) {
            $this->cpMenuAccessWrite->grantCpMenuAccess($employee->getId(), self::DDMRP_ORDER_CP_MENU_ID, $accessType);
        }
        $this->transaction->commit();
    }

    private function revokeAccess(CokEmployee $employee): void
    {
        $existingAccess = $employee->getAccessToDdmrp();

        if (!$existingAccess instanceof DdmrpEmployeeAccess) {
            return;
        }

        $this->ddmrpAccessWrite->delete($existingAccess->getId());
    }
}
