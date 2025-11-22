<?php

declare(strict_types=1);

namespace App\Domain\Dit\Reporter\UseCase;

use App\Domain\Dit\Reporter\Repository\ReportQueryRepository;
use Database\Connection\ReadDatabaseInterface;

class GetReportListUseCase
{
    public function __construct(
        protected ReadDatabaseInterface $connection,
        protected ReportQueryRepository $readRepo,
    ) {
    }

    public function getReportList(): array
    {
        return $this->readRepo->getReportList();
    }
}
