<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\DTO;

class CalculationResultResponse
{
    public function __construct(
        public int $id
    ) {
    }
}
