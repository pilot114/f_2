<?php

declare(strict_types=1);

namespace App\Domain\Finance\PointsLoan\Exporter;

use App\Domain\Finance\PointsLoan\Entity\ExcelLoanRepresentation;
use App\Domain\Finance\PointsLoan\Repository\LoanQueryRepository;
use App\Domain\Portal\Excel\Exporter\AbstractExporter;
use DateTimeImmutable;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Entity\SheetView;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class LoansExporter extends AbstractExporter
{
    public function __construct(
        private readonly LoanQueryRepository $loanQueryRepository,
        Writer $writer
    ) {
        parent::__construct($writer);
    }

    public function getExporterName(): string
    {
        return 'LoansExporter';
    }

    public function getFileName(): string
    {
        $date = date('Y-m-d_H-i-s');
        return $this->sanitizeFileName("loans_export_$date.xlsx");
    }

    public function export(array $params): void
    {
        if (!isset($params['start']) || !isset($params['end'])) {
            throw new HttpException(400, 'не переданы обязательные параметры start или end');
        }

        try {
            $start = (new DateTimeImmutable($params['start']))->modify('first day of this month');
            $end = (new DateTimeImmutable($params['end']))->modify('first day of this month');
        } catch (Throwable) {
            throw new HttpException(400, 'не удалось распознать формат дат');
        }

        $this->writer->openToBrowser($this->getFileName());

        // Задать базовые настройки для листа.
        $sheetView = new SheetView();
        $sheetView->setFreezeRow(2);
        $this->writer->getCurrentSheet()->setSheetView($sheetView);

        $data = $this->loanQueryRepository->getLoansInExcelRepresentation(
            start: $start,
            end: $end
        );

        $sheet = $this->writer->getCurrentSheet();
        $columnWidth = [
            1 => 92,
            2 => 255,
            3 => 109,
            4 => 265,
        ];
        foreach ($columnWidth as $col => $width) {
            $sheet->setColumnWidth($width / 7, $col);
        }

        //заголовки
        $style = new Style();
        $style->setFontBold();
        $headers = Row::fromValues(ExcelLoanRepresentation::getHeaders() , $style);
        $this->writer->addRow($headers);

        // данные
        /** @var ExcelLoanRepresentation $loan */
        foreach ($data as $loan) {
            $style = new Style();
            $dataRow = Row::fromValues($loan->getColumnsData(), $style);
            $this->writer->addRow($dataRow);
        }

        $this->writer->close();
    }
}
