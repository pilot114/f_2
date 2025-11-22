<?php

declare(strict_types=1);

namespace App\Domain\OperationalEfficiency\DDMRP\DTO;

use App\Common\DTO\FilterOption;

readonly class GetCokListRequest
{
    public function __construct(
        public int $countryId,
        public FilterOption|int $regionDirectorId,
        public ?string $search = null
    ) {
    }
}
