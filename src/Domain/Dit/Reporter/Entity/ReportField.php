<?php

declare(strict_types=1);

namespace App\Domain\Dit\Reporter\Entity;

readonly class ReportField
{
    public function __construct(
        public string $fieldName = '',
        public string $bandName = '',
        public string $displayLabel = '',
        public bool $isCurrency = false,
    ) {
    }
}
