<?php

declare(strict_types=1);

namespace App\Common\DTO;

class FindRequest
{
    public function __construct(
        public ?int $page = null,
        public ?int $perPage = null,
        // TODO: common find params
    ) {
    }
}
