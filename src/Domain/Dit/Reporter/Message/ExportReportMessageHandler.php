<?php

declare(strict_types=1);

namespace App\Domain\Dit\Reporter\Message;

use App\Domain\Dit\Reporter\Repository\ReportQueryRepository;
use App\Domain\Dit\Reporter\Service\ReporterEmailer;
use App\Domain\Dit\Reporter\Service\ReporterExcel;
use App\Domain\Dit\Reporter\UseCase\ExecuteReportUseCase;
use App\Domain\Portal\Security\Repository\SecurityQueryRepository;
use Database\Connection\CpConnection;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Throwable;

#[AsMessageHandler]
readonly class ExportReportMessageHandler
{
    public function __construct(
        private ExecuteReportUseCase $executeReportUseCase,
        private ReporterExcel $reporterExcel,
        private ReporterEmailer $reporterEmailer,
        private SecurityQueryRepository $securityQueryRepository,
        private ReportQueryRepository $reportQueryRepository,
        private LoggerInterface $logger,
        private CpConnection $conn,
    ) {
    }

    public function __invoke(ExportReportMessage $message): void
    {
        $currentUser = $this->securityQueryRepository->find($message->userId);
        if ($currentUser === null) {
            $this->logger->error("User not found", [
                'userId' => $message->userId,
            ]);
            return;
        }

        $this->conn->procedure('acl.pacl.authorize_from_cp', [
            'login' => $currentUser->login,
        ]);

        try {
            $this->logger->notice('Starting report execution', [
                'reportId' => $message->reportId,
                'userId'   => $currentUser->id,
                'input'    => $message->input,
            ]);
            $startTime = microtime(true);

            $report = $this->reportQueryRepository->getReport($message->reportId);

            // Выполняем отчёт и получаем данные
            $items = $this->executeReportUseCase->executeReport(
                $message->reportId,
                $currentUser,
                $message->input,
                allData: true
            )[0];

            // Генерируем Excel файл
            $file = $this->reporterExcel
                ->setName($report->getName(), $message)
                ->setContent($items, $this->executeReportUseCase->reportQuery->fields)
                ->getFile();

            $executionTime = round(microtime(true) - $startTime, 3);

            // Отправляем письмо с отчётом
            $this->reporterEmailer->sendReport(
                $report->getName(),
                $file,
                $message,
                (int) $executionTime
            );

            $this->logger->notice('Report execution completed', [
                'reportId'      => $message->reportId,
                'userId'        => $currentUser->id,
                'input'         => $message->input,
                'rowsCount'     => $this->reporterExcel->getRowsCount(),
                'executionTime' => $executionTime,
            ]);

        } catch (Throwable $e) {
            $this->logger->error('Failed to export report', [
                'reportId' => $message->reportId,
                'userId'   => $message->userId,
                'error'    => $e->getMessage(),
                'trace'    => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }
}
