<?php

declare(strict_types=1);

namespace App\Domain\Hr\Achievements\Exporter;

use App\Domain\Hr\Achievements\Repository\AchievementEmployeeItemQueryRepository;
use App\Domain\Portal\Excel\Exporter\AbstractExporter;
use OpenSpout\Common\Entity\Cell;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;
use Psr\Log\LoggerInterface;

class AchievementStructureExporter extends AbstractExporter
{
    public function __construct(
        private readonly LoggerInterface                             $logger,
        private readonly AchievementEmployeeItemQueryRepository      $repository,
        Writer                                                       $writer,
    ) {
        parent::__construct($writer);
    }

    public function getExporterName(): string
    {
        return 'AchievementStructureExporter';
    }

    public function getFileName(): string
    {
        return $this->sanitizeFileName("Achievement structure.xlsx");
    }

    public function export(array $params): void
    {
        $this->logger->info('Achievement structure', [
            'params' => $params,
        ]);
        $data = $this->repository->getAll();

        $this->writer->openToBrowser($this->getFileName());
        $this->writer->getOptions()->setColumnWidth(261 / 7, 1);
        $this->writer->getOptions()->setColumnWidth(190 / 7, 2);
        $this->writer->getOptions()->setColumnWidthForRange(25, 3, 4);

        $rowFromValues = Row::fromValues([
            'Сотрудник',
            'Должность',
            'Категория',
            'Достижение',
            'Дата получения',
        ]);
        $this->writer->addRow($rowFromValues);

        // Добавляем строки с данными
        foreach ($data as $item) {
            $dto = $item->toAchievementEmployeeItemResponse();
            $rowFromValues = new Row([
                Cell::fromValue($dto->employee->name),
                Cell::fromValue($dto->employee->positionName),
                Cell::fromValue($dto->achievement->category?->name),
                Cell::fromValue($dto->achievement->name),
                Cell::fromValue($dto->receiveDate->format('m.Y')),
                Cell::fromValue($dto->receiveDate->format('m.Y')),
            ]);
            $this->writer->addRow($rowFromValues);
        }

        $this->writer->close();
    }
}
