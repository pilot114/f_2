<?php

declare(strict_types=1);

namespace App\Domain\Hr\Achievements\Exporter;

use App\Domain\Hr\Achievements\Entity\AchievementEmployeeItem;
use App\Domain\Hr\Achievements\Repository\AchievementEmployeeItemQueryRepository;
use App\Domain\Portal\Excel\Exporter\AbstractExporter;
use OpenSpout\Common\Entity\Cell;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;
use Psr\Log\LoggerInterface;

class AchievementUserListExporter extends AbstractExporter
{
    public function __construct(
        Writer                                      $writer,
        private readonly LoggerInterface            $logger,
        protected AchievementEmployeeItemQueryRepository $repository,
    ) {
        parent::__construct($writer);
    }

    public function getExporterName(): string
    {
        return 'AchievementUnlockersExporter';
    }

    public function getFileName(): string
    {
        return $this->sanitizeFileName("Achievement unlocked.xlsx");
    }

    public function export(array $params): void
    {
        $this->logger->info('Achievement unlocked', [
            'params' => $params,
        ]);
        $id = (int) $params['achievementId'];
        $this->writer->openToBrowser($this->getFileName());
        $this->writer->getOptions()->setColumnWidth(261 / 7, 1);
        $this->writer->getOptions()->setColumnWidth(190 / 7, 2);
        $this->writer->getOptions()->setColumnWidthForRange(25, 3, 4);
        $this->writer->getOptions()->setColumnWidthForRange(20, 5, 7);

        $rowFromValues = Row::fromValues([
            'Сотрудник',
            'Должность',
            'Категория',
            'Достижение',
            'Дата получения',
            'Дата редактирования',
            'ФИО редактора',
        ]);
        $this->writer->addRow($rowFromValues);

        $data = $this->repository->getByAchievementIdWithEditor($id);

        // Добавляем строки с данными
        /** @var AchievementEmployeeItem $item */
        foreach ($data as $item) {
            $dto = $item->toAchievementEmployeeItemWithEditorResponse();
            $rowFromValues = new Row([
                Cell::fromValue($dto->employee->name),
                Cell::fromValue($dto->employee->positionName),
                Cell::fromValue($dto->achievement->category?->name),
                Cell::fromValue($dto->achievement->name),
                Cell::fromValue($dto->receiveDate->format('m.Y')),
                Cell::fromValue($dto->addedDate->format('d.m.Y H:i:s')),
                Cell::fromValue($dto->editor->name),
            ]);
            $this->writer->addRow($rowFromValues);
        }
        $this->writer->close();
    }
}
