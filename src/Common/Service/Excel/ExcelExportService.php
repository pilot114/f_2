<?php

declare(strict_types=1);

namespace App\Common\Service\Excel;

use App\Domain\Portal\Excel\Factory\ExcelExporterFactory;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;

readonly class ExcelExportService
{
    public function __construct(
        private ExcelExporterFactory $exporterFactory
    ) {
    }

    public function export(string $exporterName, array $params): StreamedResponse
    {
        $exporter = $this->exporterFactory->create($exporterName);

        $response = new StreamedResponse(static fn () => $exporter->export($params));

        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition',
            ResponseHeaderBag::DISPOSITION_ATTACHMENT . "; filename=\"{$exporter->getFileName()}\""
        );

        return $response;
    }
}
