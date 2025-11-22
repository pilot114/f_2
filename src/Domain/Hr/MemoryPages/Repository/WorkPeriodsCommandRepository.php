<?php

declare(strict_types=1);

namespace App\Domain\Hr\MemoryPages\Repository;

use App\Domain\Hr\MemoryPages\Entity\WorkPeriod;
use Database\ORM\CommandRepository;

/**
 * @extends CommandRepository<WorkPeriod>
 */
class WorkPeriodsCommandRepository extends CommandRepository
{
    protected string $entityName = WorkPeriod::class;

    public function deleteAllWorkPeriods(int $memoryPageId): void
    {
        $this->conn->delete(
            WorkPeriod::TABLE,
            [
                'PERSONAL_PAGE_ID' => $memoryPageId,
            ]
        );
    }
}
