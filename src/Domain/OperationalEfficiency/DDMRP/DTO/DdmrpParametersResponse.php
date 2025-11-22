<?php

declare(strict_types=1);

namespace App\Domain\OperationalEfficiency\DDMRP\DTO;

readonly class DdmrpParametersResponse
{
    public function __construct(
        public ?float $dvf,
        public ?int $dltf,
        public ?int $dlt,
        public ?int $reOrderPoint,
        public ?int $expirationPercent,
        public ?int $moq,
        public ?int $slt,
    ) {
    }
}
