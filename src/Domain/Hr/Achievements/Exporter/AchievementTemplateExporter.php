<?php

declare(strict_types=1);

namespace App\Domain\Hr\Achievements\Exporter;

use App\Domain\Portal\Excel\Exporter\AbstractExporter;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;
use Psr\Log\LoggerInterface;

class AchievementTemplateExporter extends AbstractExporter
{
    public function __construct(
        private readonly LoggerInterface $logger,
        Writer $writer
    ) {
        parent::__construct($writer);
    }

    public function getExporterName(): string
    {
        return 'AchievementTemplateExporter';
    }

    public function getFileName(): string
    {
        return $this->sanitizeFileName("Achievement template.xlsx");
    }

    public function export(array $params): void
    {
        $this->logger->info('Achievement template', [
            'params' => $params,
        ]);
        $this->writer->openToBrowser($this->getFileName());
        $this->writer->getOptions()->setColumnWidthForRange(40, 1,3);
        $data = self::getExampleData();

        // Добавляем строки с данными
        foreach ($data as $item) {
            $rowFromValues = Row::fromValues($item);
            $this->writer->addRow($rowFromValues);
        }
        $this->writer->close();
    }

    public static function getExampleData(): array
    {
        // ТутМожно реализовать получение реальных данных и тд
        return [
            [
                'ФИО сотрудника',
                'Дата получения',
            ],
            [
                'Вносить полностью и без ошибок!',
                'Формат даты ГГГГ-ММ-ДД',
            ],
            [
                'Например:',
                ' ',
            ],
            [
                'Сотрудник Брэд Пит',
                'Получил 11 мая 2022',
            ],
            [
                'Заполняем:',
                ' ',
                ' ',
            ],
            [
                'Пит Брэд Уильямович',
                '2022–05–11',
            ],
            [
                'После заполнения желательно удалить все строки примера.',
                ' ',
            ],
        ];
    }
}
