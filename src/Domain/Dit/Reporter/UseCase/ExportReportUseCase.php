<?php

declare(strict_types=1);

namespace App\Domain\Dit\Reporter\UseCase;

use App\Domain\Dit\Reporter\Service\ReporterExcel;
use App\Domain\Portal\Security\Entity\SecurityUser;
use App\Domain\Portal\Security\Repository\SecurityQueryRepository;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class ExportReportUseCase
{
    public function __construct(
        protected ReporterExcel $reporterExcel,
        protected ExecuteReportUseCase $executeReportUseCase,
        protected SecurityQueryRepository $access,
    ) {
    }

    public function export(int $reportId, array $input, SecurityUser $currentUser): UploadedFile
    {
        if (!$this->access->hasPermission($currentUser->id, 'rep_report', $reportId)) {
            throw new AccessDeniedHttpException("Нет прав на отчёт $reportId");
        }

        $items = $this->executeReportUseCase->executeReport($reportId, $currentUser, $input, allData: true)[0];

        return $this->reporterExcel
            ->setName("Отчёт $reportId")
            ->setContent($items, $this->executeReportUseCase->reportQuery->fields)
            ->getFile();
    }
}
