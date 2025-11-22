<?php

declare(strict_types=1);

namespace App\Common\Service\Excel;

use App\Common\Service\File\TempFileRegistry;
use App\Gateway\WriteExcelGateway;
use Closure;
use RuntimeException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

abstract class BaseCommandExcelService
{
    protected string $fileName;
    protected int $rowsCount;

    public function __construct(
        private WriteExcelGateway $writer,
        private TempFileRegistry $tempFileRegistry,
    ) {
    }

    public function getRowsCount(): int
    {
        return $this->rowsCount;
    }

    public function getFile(): UploadedFile
    {
        $file = $this->tempFileRegistry->createUploadedFile("{$this->fileName}.xlsx");
        $this->writeFile($file->getPathname());
        return $file;
    }

    public function clear(): static
    {
        $this->writer->clear();
        return $this;
    }

    /**
     * @param array<int, string> $tabs
     */
    protected function setTabs(array $tabs): static
    {
        foreach ($tabs as $i => $tab) {
            if ($i === 0) {
                $this->writer->setTitle($tab);
            } else {
                $this->writer->addSheet($tab);
            }
        }
        return $this;
    }

    /**
     * @param iterable<array> $items
     */
    protected function eachItem(Closure $fn, iterable $items): static
    {
        foreach ($items as $i => $item) {
            $row = $i + 2;
            $data = $fn($item, $row);

            if ($i === 0) {
                $this->writer->setData(array_keys($data));
            }
            $this->writer->setData(array_values($data), "A$row");
            $this->rowsCount = $row;
        }
        return $this;
    }

    protected function selectSheet(string $sheetName): static
    {
        $this->writer->selectSheet($sheetName);
        return $this;
    }

    protected function writeFile(string $name, string $type = 'Xlsx'): void
    {
        $this->writer->writeFile($name, $type);
    }

    protected function setDefaultConfig(): static
    {
        try {
            // Пытаемся использовать итераторы (работает для PhpSpreadsheet)
            foreach ($this->writer->columns() as $column) {
                $this->writer->setAutoSize($column->getColumnIndex());
            }
            foreach ($this->writer->rows() as $row) {
                if ($row->getRowIndex() === 1) {
                    $this->writer->setHeader($row);
                }
            }
        } catch (RuntimeException $e) {
            // OpenSpout не поддерживает итерацию по колонкам/строкам
            // Устанавливаем автоширину для первых 30 колонок (достаточно для большинства отчетов)
            for ($i = 0; $i < 30; $i++) {
                $columnLetter = $this->writer->indexToLetter($i);
                $this->writer->setAutoSize($columnLetter);
            }

            // Создаем фиктивный объект строки для setHeader
            $mockRow = new class() {
                public function getRowIndex(): int
                {
                    return 1;
                }
            };
            $this->writer->setHeader($mockRow);
        }
        return $this;
    }
}
