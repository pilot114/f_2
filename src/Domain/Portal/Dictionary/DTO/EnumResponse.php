<?php

declare(strict_types=1);

namespace App\Domain\Portal\Dictionary\DTO;

readonly class EnumResponse
{
    public function __construct(
        public string $domain,
        public string $name,
        /** @var array<EnumCaseResponse> */
        public array  $cases,
    ) {
    }
}
