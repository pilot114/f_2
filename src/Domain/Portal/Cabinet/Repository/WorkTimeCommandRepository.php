<?php

declare(strict_types=1);

namespace App\Domain\Portal\Cabinet\Repository;

use App\Domain\Portal\Cabinet\Entity\WorkTime;
use Database\Connection\ParamType;
use Database\ORM\CommandRepository;

/**
 * @extends CommandRepository<WorkTime>
 */
class WorkTimeCommandRepository extends CommandRepository
{
    protected string $entityName = WorkTime::class;

    public function updateWorkTime(WorkTime $workTime): void
    {
        $this->conn->update(
            WorkTime::TABLE,
            [
                'time_start' => $workTime->getStart(),
                'time_end'   => $workTime->getEnd(),
                'timezone'   => $workTime->getTimeZone()->value,
            ],
            [
                'id' => $workTime->getId(),
            ],
            [
                'time_start' => ParamType::DATE,
                'time_end'   => ParamType::DATE,
            ]
        );
    }
}
