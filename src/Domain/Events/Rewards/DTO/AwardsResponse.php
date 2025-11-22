<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\DTO;

class AwardsResponse
{
    public function __construct(
        public string                      $name,
        public CalculationResultIdResponse $calculationResult,
        public ?string                     $comment,
        public int                         $count,
        public StatusResponse              $status
    ) {
    }
}
