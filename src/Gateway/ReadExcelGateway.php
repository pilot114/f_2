<?php

declare(strict_types=1);

namespace App\Gateway;

interface ReadExcelGateway extends ExcelGateway
{
    /**
     * Получает значение из определенной ячейки.
     */
    public function getCellValue(string $cellAddress): mixed;

    /**
     * Получает значения из определенной строки.
     */
    public function getRowValues(int $rowNumber): array;

    /**
     * Получает значения из определенного столбца.
     */
    public function getColumnValues(string $columnLetter): array;

    /**
     * Получает все данные в виде 2d массива.
     */
    public function getData(?string $startCellAddress = null, ?string $endCellAddress = null): array;
}
