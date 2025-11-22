<?php

declare(strict_types=1);

namespace App\Domain\OperationalEfficiency\DDMRP\DTO;

readonly class CokEmployeesResponse
{
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public ?string $phone,
        public ?ResponseResponse $response,
        public bool $accessToDddmrp,
    ) {
    }
}
