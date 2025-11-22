<?php

declare(strict_types=1);

namespace App\Domain\OperationalEfficiency\DDMRP\Entity;

use Database\ORM\Attribute\Column;
use Database\ORM\Attribute\Entity;

#[Entity(name: self::TABLE_NAME, sequenceName: self::SQ_NAME)]
class DdmrpEmployeeAccess
{
    public const TABLE_NAME = 'TEHNO.DDMRP_COK_EMPLOYERS';
    public const SQ_NAME = 'TEHNO.DDMRP_COK_EMPLOYERS_SQ';

    public function __construct(
        #[Column] private int $id,
        #[Column] private string $contract,
        #[Column(name: 'cp_emp_id')] private int $cpEmpId,
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }
}
