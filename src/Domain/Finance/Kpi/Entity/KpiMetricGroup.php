<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\Entity;

use Database\ORM\Attribute\Column;
use Database\ORM\Attribute\Entity;

#[Entity('tehno.kpi_metric_group')]
class KpiMetricGroup
{
    public function __construct(
        #[Column] protected int $id,
        #[Column] protected string $name,
    ) {
    }

    public function toArray(): array
    {
        return [
            'id'   => $this->id,
            'name' => $this->name,
        ];
    }
}
