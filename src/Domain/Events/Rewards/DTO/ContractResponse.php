<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\DTO;

class ContractResponse
{
    public function __construct(
        public string $name,
        public string $contract,
        public bool $isFamily,
    ) {
    }
}
