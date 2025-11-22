<?php

declare(strict_types=1);

namespace App\Gateway;

interface WriteExcelGateway extends ExcelGateway
{
    /**
     * Сохраняет Excel-файл по указанному пути.
     */
    public function writeFile(string $filePath, string $type = 'Xlsx'): void;

    /**
     * Устанавливает значение в определенную ячейку.
     */
    public function setCellValue(string $cellAddress, mixed $value): void;

    /**
     * Записывает массив данных в указанную строку.
     */
    public function writeRow(int $rowNumber, array $data): void;

    /**
     * Записывает массив данных в указанный столбец.
     */
    public function writeColumn(string $columnLetter, array $data): void;

    /**
     * Устанавливает массив данных из 2d массива
     */
    public function setData(array $data, string $startCellAddress = 'A1'): self;

    /**
     * Очистить состояние
     */
    public function clear(): self;

    /**
     * Устанавливает автоматическое определение ширины на колонку
     */
    public function setAutoSize(string $letter): self;

    /**
     * Устанавливает имя текущему листу.
     */
    public function setTitle(string $sheetName): self;

    /**
     * Добавляет новый лист в книгу.
     */
    public function addSheet(string $sheetName): void;

    /**
     * Удаляет лист из книги.
     */
    public function removeSheet(string $sheetName): void;

    /**
     * Установка стилей заголовка на строку
     * @param object $row PhpSpreadsheet Row или объект с методом getRowIndex()
     */
    public function setHeader(object $row): void;
}
