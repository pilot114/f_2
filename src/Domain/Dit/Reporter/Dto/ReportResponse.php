<?php

declare(strict_types=1);

namespace App\Domain\Dit\Reporter\Dto;

class ReportResponse
{
    public function __construct(
        public array $items,
        public string $keyField,
        public string $detailField,
        public string $masterField,
        public int $total = 0,
    ) {
    }
}
