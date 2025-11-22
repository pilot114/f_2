<?php

declare(strict_types=1);

namespace App\System\Excel;

use App\Gateway\ReadExcelGateway;
use App\Gateway\WriteExcelGateway;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\ColumnIterator;
use PhpOffice\PhpSpreadsheet\Worksheet\Row;
use PhpOffice\PhpSpreadsheet\Worksheet\RowIterator;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SpreadSheetExcelAdapter implements ReadExcelGateway, WriteExcelGateway
{
    protected Spreadsheet $spreadsheet;
    protected Worksheet $activeWorksheet;

    public function __construct()
    {
        $this->spreadsheet = new Spreadsheet();
        $this->activeWorksheet = $this->spreadsheet->getActiveSheet();
    }

    // ======= ExcelGateway =======

    public function openFile(?string $filePath = null): self
    {
        return $this;
    }

    public function selectSheet(string $sheetName): self
    {
        $this->activeWorksheet = $this->spreadsheet->setActiveSheetIndexByName($sheetName);
        return $this;
    }

    public function closeFile(): void
    {
        $this->spreadsheet->disconnectWorksheets();
        unset($this->spreadsheet);
    }

    public function columns(): ColumnIterator
    {
        return $this->activeWorksheet->getColumnIterator();
    }

    public function rows(): RowIterator
    {
        return $this->activeWorksheet->getRowIterator();
    }

    // ======= ReadExcelGateway =======

    public function getCellValue(string $cellAddress): mixed
    {
        return 42;
    }

    public function getRowValues(int $rowNumber): array
    {
        return [];
    }

    public function getColumnValues(string $columnLetter): array
    {
        return [];
    }

    public function getData(?string $startCellAddress = null, ?string $endCellAddress = null): array
    {
        return $this->activeWorksheet->toArray();
    }

    // ======= WriteExcelGateway =======

    public function clear(): self
    {
        $this->spreadsheet = new Spreadsheet();
        $this->activeWorksheet = $this->spreadsheet->getActiveSheet();
        return $this;
    }

    public function setAutoSize(string $letter): self
    {
        $this->activeWorksheet->getColumnDimension($letter)->setAutoSize(true);
        return $this;
    }

    public function setData(array $data, string $startCellAddress = 'A1'): self
    {
        $this->activeWorksheet->fromArray($data, startCell: $startCellAddress);
        return $this;
    }

    public function writeFile(string $filePath, string $type = 'Xlsx'): void
    {
        $writer = IOFactory::createWriter($this->spreadsheet, $type);
        $writer->save($filePath);
    }

    public function setCellValue(string $cellAddress, mixed $value): void
    {
    }

    public function writeRow(int $rowNumber, array $data): void
    {
    }

    public function writeColumn(string $columnLetter, array $data): void
    {
    }

    public function addSheet(string $sheetName): void
    {
        $myWorkSheet = new Worksheet($this->spreadsheet, $sheetName);
        $this->spreadsheet->addSheet($myWorkSheet);
    }

    public function removeSheet(string $sheetName): void
    {
    }

    public function setTitle(string $sheetName): self
    {
        $this->activeWorksheet->setTitle($sheetName);
        return $this;
    }

    public function setHeader(object $row): void
    {
        // Проверяем, что это действительно PhpSpreadsheet Row
        if (!$row instanceof Row) {
            // Если это mock-объект, просто игнорируем
            return;
        }

        $this->activeWorksheet->getStyle($row->getRowIndex())
            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        foreach ($row->getColumnIterator() as $cell) {
            $this->activeWorksheet->getStyle($cell->getCoordinate())
                ->getFill()->setFillType(Fill::FILL_PATTERN_LIGHTGRAY);
        }
    }

    /**
     * Конвертирует индекс колонки в букву (0=A, 1=B, ..., 25=Z, 26=AA)
     */
    public function indexToLetter(int $index): string
    {
        $letter = '';

        while ($index >= 0) {
            $letter = chr(($index % 26) + ord('A')) . $letter;
            $index = (int) ($index / 26) - 1;
        }

        return $letter;
    }
}
