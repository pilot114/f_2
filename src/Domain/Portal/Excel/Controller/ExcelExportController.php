<?php

declare(strict_types=1);

namespace App\Domain\Portal\Excel\Controller;

use App\Common\Service\Excel\ExcelExportService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;

readonly class ExcelExportController
{
    public function __construct(
        private ExcelExportService $excelExportService
    ) {
    }

    #[Route('/api/v2/excel', name: 'excel_export', methods: ['POST'])]
    public function export(Request $request): StreamedResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!is_array($data)) {
            throw new BadRequestHttpException('Невалидный JSON');
        }

        $exporterName = $data['exporter'] ?? null;
        $params = $data['params'] ?? [];

        if (!is_string($exporterName) || ($exporterName === '' || $exporterName === '0')) {
            throw new BadRequestHttpException('Параметр "exporter" обязателен');
        }

        if (!is_array($params)) {
            throw new BadRequestHttpException('Параметр "params" должен быть массивом');
        }

        return $this->excelExportService->export($exporterName, $params);
    }
}
