<?php

declare(strict_types=1);

namespace App\Domain\OperationalEfficiency\DDMRP\DTO;

use Symfony\Component\Validator\Constraints as Assert;

readonly class DdmrpParameters
{
    public function __construct(
        #[Assert\Range(min: 0, max: 9999)]
        public ?float $dvf = null,

        #[Assert\Range(min: 0, max: 9999)]
        public ?int $dltf = null,

        #[Assert\Range(min: 0, max: 9999)]
        public ?int $dlt = null,

        #[Assert\Range(min: 0, max: 9999)]
        public ?int $reOrderPoint = null,

        #[Assert\Range(min: 0, max: 100)]
        public ?int $expirationPercent = null,

        #[Assert\Range(min: 0, max: 9999)]
        public ?int $moq = null,

        public ?int $slt = null,
    ) {
    }
}
