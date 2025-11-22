<?php

declare(strict_types=1);

namespace App\Domain\Hr\Achievements\DTO;

readonly class UnlockFromExcelResponse
{
    public function __construct(
        public array $errors,
        public int $linesInFile,
        public int $successCount
    ) {
    }
}
