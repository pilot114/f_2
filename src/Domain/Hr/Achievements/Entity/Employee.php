<?php

declare(strict_types=1);

namespace App\Domain\Hr\Achievements\Entity;

use App\Domain\Hr\Achievements\DTO\EmployeeResponse;
use Database\ORM\Attribute\Column;
use Database\ORM\Attribute\Entity;

#[Entity(name: 'TEST.CP_EMP', sequenceName: 'TEST.CP_EMP_SQ')]
class Employee
{
    public function __construct(
        #[Column(name: 'id')] public int           $id,
        #[Column(name: 'name')] private string     $name,
        #[Column(name: 'response')] private string $positionName,
    ) {
    }

    public function toEmployeeResponse(): EmployeeResponse
    {
        return new EmployeeResponse(
            id: $this->id,
            name: $this->name,
            positionName: trim($this->positionName)
        );
    }
}
