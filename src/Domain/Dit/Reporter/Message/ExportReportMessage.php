<?php

declare(strict_types=1);

namespace App\Domain\Dit\Reporter\Message;

readonly class ExportReportMessage
{
    public function __construct(
        public int $reportId,
        public array $input,
        public int $userId,
        public string $userEmail,
    ) {
    }
}
