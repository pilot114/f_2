<?php

declare(strict_types=1);

namespace App\System\Excel;

use App\Gateway\WriteExcelGateway;
use OpenSpout\Common\Entity\Cell;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Options;
use OpenSpout\Writer\XLSX\Writer;
use PhpOffice\PhpSpreadsheet\Worksheet\ColumnIterator;
use PhpOffice\PhpSpreadsheet\Worksheet\RowIterator;
use RuntimeException;

/**
 * Реализация WriteExcelGateway на основе OpenSpout для оптимизации памяти
 * OpenSpout использует потоковую запись, что позволяет обрабатывать большие файлы
 */
class OpenSpoutExcelAdapter implements WriteExcelGateway
{
    /** @var array<string, array<int, array>> Данные листов [sheetName => [rowIndex => rowData]] */
    private array $sheets = [];

    /** @var array<string, array<string, bool>> Настройки автоширины колонок [sheetName => [column => true]] */
    private array $autoSizeColumns = [];

    /** @var array<string, int> Индексы строк-заголовков [sheetName => rowIndex] */
    private array $headerRows = [];

    /** @var array<string, array<int, float>> Максимальная ширина колонок [sheetName => [columnIndex => width]] */
    private array $columnWidths = [];

    private string $activeSheetName = 'Sheet1';

    private ?Writer $writer = null;

    public function __construct()
    {
        $this->clear();
    }

    // ======= ExcelGateway =======

    public function openFile(?string $filePath = null): self
    {
        return $this;
    }

    public function selectSheet(string $sheetName): self
    {
        if (!isset($this->sheets[$sheetName])) {
            throw new RuntimeException("Sheet '$sheetName' does not exist");
        }
        $this->activeSheetName = $sheetName;
        return $this;
    }

    public function closeFile(): void
    {
        if ($this->writer instanceof Writer) {
            $this->writer->close();
            $this->writer = null;
        }
    }

    public function columns(): ColumnIterator
    {
        // OpenSpout не поддерживает итерацию по колонкам
        // Возвращаем пустой итератор для совместимости
        throw new RuntimeException('OpenSpout does not support column iteration. Use PhpSpreadsheet wrapper instead.');
    }

    public function rows(): RowIterator
    {
        // OpenSpout не поддерживает итерацию по строкам после записи
        // Этот метод используется для форматирования, которое мы обработаем при записи
        throw new RuntimeException('OpenSpout does not support row iteration. Use PhpSpreadsheet wrapper instead.');
    }

    // ======= WriteExcelGateway =======

    public function clear(): self
    {
        $this->sheets = [
            'Sheet1' => [],
        ];
        $this->autoSizeColumns = [
            'Sheet1' => [],
        ];
        $this->headerRows = [];
        $this->columnWidths = [
            'Sheet1' => [],
        ];
        $this->activeSheetName = 'Sheet1';

        if ($this->writer instanceof Writer) {
            $this->writer->close();
            $this->writer = null;
        }

        return $this;
    }

    public function setAutoSize(string $letter): self
    {
        $this->autoSizeColumns[$this->activeSheetName][$letter] = true;
        return $this;
    }

    public function setData(array $data, string $startCellAddress = 'A1'): self
    {
        // Проверяем, является ли $data одномерным массивом (для совместимости с PhpSpreadsheet)
        // Если все элементы скалярные или первый элемент не массив, считаем одномерным
        if ($data !== [] && !is_array(reset($data))) {
            // Преобразуем одномерный массив в двумерный (одна строка)
            $data = [$data];
        }

        // Парсим адрес ячейки (например, "A1" -> column: 0, row: 0)
        preg_match('/([A-Z]+)(\d+)/', $startCellAddress, $matches);
        $columnLetter = $matches[1] ?? 'A';
        $startRow = (int) ($matches[2] ?? 1);
        $startColumn = $this->columnLetterToIndex($columnLetter);

        foreach ($data as $rowIndex => $rowData) {
            $targetRow = $startRow + $rowIndex;

            if (!isset($this->sheets[$this->activeSheetName][$targetRow])) {
                $this->sheets[$this->activeSheetName][$targetRow] = [];
            }

            // Если rowData все еще не массив (например, скалярное значение), преобразуем
            if (!is_array($rowData)) {
                $rowData = [$rowData];
            }

            foreach ($rowData as $colIndex => $cellValue) {
                $targetCol = $startColumn + $colIndex;
                $this->sheets[$this->activeSheetName][$targetRow][$targetCol] = $cellValue;

                // Отслеживаем ширину колонки для автоширины
                $cellWidth = $this->calculateCellWidth($cellValue);
                if (!isset($this->columnWidths[$this->activeSheetName][$targetCol])) {
                    $this->columnWidths[$this->activeSheetName][$targetCol] = $cellWidth;
                } else {
                    $this->columnWidths[$this->activeSheetName][$targetCol] = max(
                        $this->columnWidths[$this->activeSheetName][$targetCol],
                        $cellWidth
                    );
                }
            }
        }

        return $this;
    }

    public function writeFile(string $filePath, string $type = 'Xlsx'): void
    {
        $options = new Options();
        $options->DEFAULT_COLUMN_WIDTH = 15;
        $options->DEFAULT_ROW_HEIGHT = 15;

        $writer = new Writer($options);
        $writer->openToFile($filePath);
        $this->writer = $writer;

        $isFirstSheet = true;
        foreach ($this->sheets as $sheetName => $rows) {
            // Создаем или переключаемся на лист
            if ($isFirstSheet) {
                $sheet = $writer->getCurrentSheet();
                $sheet->setName($sheetName);
                $isFirstSheet = false;
            } else {
                $sheet = $writer->addNewSheetAndMakeItCurrent();
                $sheet->setName($sheetName);
            }

            // Применяем ширину колонок
            $this->applyColumnWidths($sheetName);

            // Определяем максимальные индексы
            if (empty($rows)) {
                continue;
            }

            ksort($rows);

            // Записываем строки
            foreach ($rows as $rowIndex => $rowData) {
                if (empty($rowData)) {
                    continue;
                }

                ksort($rowData);

                // Создаем стиль для заголовка
                $isHeader = isset($this->headerRows[$sheetName]) && $this->headerRows[$sheetName] === $rowIndex;

                // Формируем массив ячеек
                $cells = [];
                $maxCol = max(array_keys($rowData));
                for ($col = 0; $col <= $maxCol; $col++) {
                    $value = $rowData[$col] ?? '';

                    // Применяем стиль для заголовка
                    if ($isHeader) {
                        $cellStyle = (new Style())
                            ->setFontBold()
                            ->setFontSize(11)
                            ->setBackgroundColor('D3D3D3') // Светло-серый
                            ->setShouldWrapText(false);
                        $cells[] = Cell::fromValue($value, $cellStyle);
                    } else {
                        $cells[] = Cell::fromValue($value);
                    }
                }

                $row = new Row($cells);
                $writer->addRow($row);
            }
        }

        $writer->close();
        $this->writer = null;
    }

    public function setCellValue(string $cellAddress, mixed $value): void
    {
        preg_match('/([A-Z]+)(\d+)/', $cellAddress, $matches);
        $columnLetter = $matches[1] ?? 'A';
        $row = (int) ($matches[2] ?? 1);
        $column = $this->columnLetterToIndex($columnLetter);

        $this->sheets[$this->activeSheetName][$row][$column] = $value;
    }

    public function writeRow(int $rowNumber, array $data): void
    {
        foreach ($data as $colIndex => $value) {
            $this->sheets[$this->activeSheetName][$rowNumber][$colIndex] = $value;
        }
    }

    public function writeColumn(string $columnLetter, array $data): void
    {
        $column = $this->columnLetterToIndex($columnLetter);
        foreach ($data as $rowIndex => $value) {
            $this->sheets[$this->activeSheetName][$rowIndex + 1][$column] = $value;
        }
    }

    public function addSheet(string $sheetName): void
    {
        $this->sheets[$sheetName] = [];
        $this->autoSizeColumns[$sheetName] = [];
        $this->columnWidths[$sheetName] = [];
    }

    public function removeSheet(string $sheetName): void
    {
        unset($this->sheets[$sheetName]);
        unset($this->autoSizeColumns[$sheetName]);
        unset($this->headerRows[$sheetName]);
        unset($this->columnWidths[$sheetName]);
    }

    public function setTitle(string $sheetName): self
    {
        // Переименовываем текущий лист
        if (isset($this->sheets[$this->activeSheetName])) {
            $data = $this->sheets[$this->activeSheetName];
            unset($this->sheets[$this->activeSheetName]);
            $this->sheets[$sheetName] = $data;

            if (isset($this->autoSizeColumns[$this->activeSheetName])) {
                $this->autoSizeColumns[$sheetName] = $this->autoSizeColumns[$this->activeSheetName];
                unset($this->autoSizeColumns[$this->activeSheetName]);
            }

            if (isset($this->headerRows[$this->activeSheetName])) {
                $this->headerRows[$sheetName] = $this->headerRows[$this->activeSheetName];
                unset($this->headerRows[$this->activeSheetName]);
            }

            if (isset($this->columnWidths[$this->activeSheetName])) {
                $this->columnWidths[$sheetName] = $this->columnWidths[$this->activeSheetName];
                unset($this->columnWidths[$this->activeSheetName]);
            }

            $this->activeSheetName = $sheetName;
        }

        return $this;
    }

    public function setHeader(object $row): void
    {
        // Сохраняем индекс строки-заголовка для форматирования при записи
        // Принимаем object вместо PhpSpreadsheetRow для совместимости с mock-объектами
        if (method_exists($row, 'getRowIndex')) {
            $this->headerRows[$this->activeSheetName] = $row->getRowIndex();
        }
    }

    // ======= Вспомогательные методы =======

    /**
     * Конвертирует букву колонки в индекс (A=0, B=1, ..., Z=25, AA=26)
     */
    private function columnLetterToIndex(string $letter): int
    {
        $index = 0;
        $length = strlen($letter);

        for ($i = 0; $i < $length; $i++) {
            $index = $index * 26 + (ord($letter[$i]) - ord('A'));
        }

        return $index;
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

    /**
     * Вычисляет приблизительную ширину ячейки на основе содержимого
     * Примерное соотношение: 1 символ ≈ 1.2 единицы ширины Excel
     */
    private function calculateCellWidth(mixed $value): float
    {
        if ($value === null || $value === '') {
            return 5; // Минимальная ширина
        }

        // Безопасное приведение к строке
        if (is_scalar($value) || (is_object($value) && method_exists($value, '__toString'))) {
            $stringValue = (string) $value;
        } else {
            // Для массивов и объектов без __toString используем фиксированную ширину
            return 15;
        }

        $length = mb_strlen($stringValue);

        // Базовая ширина на основе длины строки
        $width = $length * 1.2;

        // Добавляем немного запаса
        $width += 2;

        // Ограничиваем максимальную ширину
        return min($width, 50);
    }

    /**
     * Применяет ширину колонок к текущему листу
     * OpenSpout не поддерживает setAutoSize напрямую, поэтому мы устанавливаем
     * вычисленную ширину через setColumnWidth
     */
    private function applyColumnWidths(string $sheetName): void
    {
        if (!isset($this->columnWidths[$sheetName]) || empty($this->columnWidths[$sheetName])) {
            return;
        }

        foreach ($this->columnWidths[$sheetName] as $columnIndex => $width) {
            $columnLetter = $this->indexToLetter($columnIndex);

            // Проверяем, нужна ли автоширина для этой колонки
            $shouldAutoSize = $this->autoSizeColumns[$sheetName][$columnLetter] ?? false;

            if ($shouldAutoSize && $this->writer instanceof Writer) {
                // OpenSpout использует метод setColumnWidth для установки ширины
                // Ширина в OpenSpout измеряется в символах (примерно)
                $sheet = $this->writer->getCurrentSheet();
                // OpenSpout использует 1-based индексы, минимум 1
                $columnNumber = max(1, $columnIndex + 1);
                $sheet->setColumnWidth($width, $columnNumber);
            }
        }
    }
}
