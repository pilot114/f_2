<?php

declare(strict_types=1);

namespace App\Domain\OperationalEfficiency\DDMRP\DTO;

use App\Common\Attribute\RpcParam;

readonly class ChangeEmployeeAccessRequest
{
    public function __construct(
        #[RpcParam('контракт ЦОКа')]
        public string $contract,
        #[RpcParam('id сотрудника')]
        public int $employeeId,
        #[RpcParam('предоставить доступ')]
        public bool $grantAccess,
    ) {
    }
}
