<?php

declare(strict_types=1);

namespace App\Domain\OperationalEfficiency\DDMRP\Entity;

use App\Domain\OperationalEfficiency\DDMRP\DTO\CokEmployeesResponse;
use Database\ORM\Attribute\Column;
use Database\ORM\Attribute\Entity;

#[Entity('test.cp_emp')]
class CokEmployee
{
    public function __construct(
        #[Column(name: 'id')] private int           $id,
        #[Column(name: 'cok_contract')] private string $cokContract,
        #[Column(name: 'department_id')] public readonly int $departmentId,
        #[Column(name: 'name')] private string      $name,
        #[Column] private ?Response $response,
        #[Column] private string $email,
        #[Column] private ?string $phone,
        #[Column(name: 'access_to_ddmrp')] private ?DdmrpEmployeeAccess $accessToDdmrp,
    ) {
    }

    public function toCokEmployeesResponse(): CokEmployeesResponse
    {
        return new CokEmployeesResponse(
            id: $this->id,
            name: $this->name,
            email: $this->email,
            phone: $this->phone,
            response: $this->response?->toResponseResponse(),
            accessToDddmrp: $this->accessToDdmrp instanceof DdmrpEmployeeAccess
        );
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getAccessToDdmrp(): ?DdmrpEmployeeAccess
    {
        return $this->accessToDdmrp;
    }

    public function getCokContract(): string
    {
        return $this->cokContract;
    }
}
