<?php

declare(strict_types=1);

namespace App\Domain\Portal\Excel\Exporter;

use OpenSpout\Common\Entity\Cell;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\CellAlignment;
use OpenSpout\Common\Entity\Style\CellVerticalAlignment;
use OpenSpout\Common\Entity\Style\Color;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\AutoFilter;
use OpenSpout\Writer\XLSX\Entity\SheetView;
use OpenSpout\Writer\XLSX\Writer;
use Psr\Log\LoggerInterface;

class ExampleExporter extends AbstractExporter
{
    public function __construct(
        private readonly LoggerInterface $logger,
        Writer $writer
    ) {
        parent::__construct($writer);
    }

    public function getExporterName(): string
    {
        return 'ExampleExporter';
    }

    public function getFileName(): string
    {
        $date = date('Y-m-d_H-i-s');
        return $this->sanitizeFileName("example_export_$date.xlsx");
    }

    public function export(array $params): void
    {
        $this->logger->info('Экспорт примера данных', [
            'params' => $params,
        ]);
        $this->writer->openToBrowser($this->getFileName());

        // Задать базовые настройки для листа.
        $sheetView = new SheetView();
        $sheetView->setFreezeRow(2);
        $this->writer->getCurrentSheet()->setSheetView($sheetView);

        //Можно задать автофильтр
        $autoFilter = new AutoFilter(0, 1, 4, 3);
        $this->writer->getCurrentSheet()->setAutoFilter($autoFilter);

        $data = $this->getExampleData();

        // Добавляем строки с данными
        foreach ($data as $item) {
            //Можно записать строку целиком и применить стили к строке.
            $style = new Style();
            $style->setFontBold();
            $style->setFontSize(15);
            $style->setFontColor(Color::BLUE);
            $style->setShouldWrapText();
            $style->setCellAlignment(CellAlignment::RIGHT);
            $style->setCellVerticalAlignment(CellVerticalAlignment::BOTTOM);
            $style->setBackgroundColor(Color::YELLOW);
            $dataRow = Row::fromValues($item, $style);
            $this->writer->addRow($dataRow);
        }

        $zebraBlackStyle = new Style();
        $zebraBlackStyle->setBackgroundColor(Color::BLACK);
        $zebraBlackStyle->setFontColor(Color::WHITE);
        $zebraBlackStyle->setFontSize(10);

        //Можно создать ячейки с определёнными стилями
        $cells = [];
        foreach ($data[count($data) - 1] as $item) {
            $cells[] = Cell::fromValue($item, $zebraBlackStyle);
        }
        $this->writer->addRow(new Row($cells));

        $sheet = $this->writer->getCurrentSheet();

        // Можно задать определенную ширину для определенных колонок или диапазона колонок на уровне листа
        $sheet->setColumnWidth(20, 1, 2);
        $sheet->setColumnWidthForRange(40, 3, 4);

        //Можно объединить ячейки А2-B2
        $this->writer->getOptions()->mergeCells(0, 2, 1, 2, $sheet->getIndex());
        //Можно объединить ячейки А3-B3
        $this->writer->getOptions()->mergeCells(0, 3, 1, 3, $sheet->getIndex());

        $this->writer->close();
    }

    private function getExampleData(): array
    {
        // ТутМожно реализовать получение реальных данных и тд
        return [
            [
                'ID',
                'Название',
                'Дата создания',
                'Статус',
                'Сумма',
            ],
            [
                'id'         => 1,
                'title'      => 'Пример записи 1',
                'created_at' => '2024-01-15',
                'status'     => 'Активна',
                'amount'     => '5000.00',
            ],
            [
                'id'         => 2,
                'title'      => 'Пример записи 2',
                'created_at' => '2024-01-20',
                'status'     => 'Завершена',
                'amount'     => '10000.00',
            ],
            [
                'id'         => 3,
                'title'      => 'Пример записи 3',
                'created_at' => '2024-01-25',
                'status'     => 'В процессе',
                'amount'     => '7500.00',
            ],
        ];
    }
}
