<?php

declare(strict_types=1);

namespace App\Domain\OperationalEfficiency\DDMRP\Entity;

use App\Domain\OperationalEfficiency\DDMRP\DTO\ResponseResponse;
use Database\ORM\Attribute\Column;
use Database\ORM\Attribute\Entity;

#[Entity('test.cp_emp_job_ref')]
class Response
{
    public function __construct(
        #[Column(name: 'id')] public int           $id,
        #[Column(name: 'name')] private string     $name,
    ) {
    }

    public function toResponseResponse(): ResponseResponse
    {
        return new ResponseResponse(
            id: $this->id,
            name: $this->name,
        );
    }
}
