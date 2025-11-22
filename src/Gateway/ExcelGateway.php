<?php

declare(strict_types=1);

namespace App\Gateway;

use PhpOffice\PhpSpreadsheet\Worksheet\ColumnIterator;
use PhpOffice\PhpSpreadsheet\Worksheet\RowIterator;

interface ExcelGateway
{
    /**
     * Создает новый Excel-файл или открывает существующий.
     */
    public function openFile(?string $filePath = null): self;

    /**
     * Переключается на указанный лист.
     */
    public function selectSheet(string $sheetName): self;

    /**
     * Закрывает файл и освобождает ресурсы.
     */
    public function closeFile(): void;

    /**
     * Перебор колонок.
     */
    public function columns(): ColumnIterator;

    /**
     * Перебор строк.
     */
    public function rows(): RowIterator;

    /**
     * Конвертирует индекс колонки в букву (0=A, 1=B, ..., 25=Z, 26=AA)
     */
    public function indexToLetter(int $index): string;
}
