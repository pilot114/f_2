<?php

declare(strict_types=1);

namespace App\Domain\OperationalEfficiency\DDMRP\DTO;

readonly class CalculationStatusResponse
{
    public function __construct(
        public ?int $id,
        public string $name
    ) {
    }
}
