<?php

declare(strict_types=1);

namespace App\Domain\Dit\Reporter\Entity;

class ReportParam
{
    public function __construct(
        public string $name = '',
        public string $caption = '',
        public string $dataType = '',
        public string $defaultValue = '',
        public int $dictionaryId = 0,
        public string $customValues = '',
        public bool $required = false,
    ) {
    }
}
