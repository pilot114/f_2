<?php

declare(strict_types=1);

namespace App\Domain\Dit\Reporter\UseCase;

use App\Domain\Dit\Reporter\Entity\Report;
use App\Domain\Dit\Reporter\Repository\ReportQueryRepository;
use App\Domain\Portal\Security\Entity\SecurityUser;
use App\Domain\Portal\Security\Repository\SecurityQueryRepository;
use Database\Connection\ReadDatabaseInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class GetReportUseCase
{
    public function __construct(
        protected ReportQueryRepository $reportQueryRepository,
        protected ReadDatabaseInterface $connection,
        protected SecurityQueryRepository $access,
    ) {
    }

    public function getReport(int $reportId, SecurityUser $currentUser): Report
    {
        if (!$this->access->hasPermission($currentUser->id, 'rep_report', $reportId)) {
            throw new AccessDeniedHttpException("Нет прав на отчёт $reportId");
        }

        return $this->reportQueryRepository->getReport($reportId);
    }
}
