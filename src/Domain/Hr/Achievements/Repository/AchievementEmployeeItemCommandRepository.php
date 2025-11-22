<?php

declare(strict_types=1);

namespace App\Domain\Hr\Achievements\Repository;

use App\Domain\Hr\Achievements\Entity\AchievementEmployeeItem;
use Database\Connection\ParamType;
use Database\ORM\CommandRepository;

/**
 * @extends CommandRepository<AchievementEmployeeItem>
 */
class AchievementEmployeeItemCommandRepository extends CommandRepository
{
    protected string $entityName = AchievementEmployeeItem::class;

    public function insert(array $data): void
    {
        $this->conn->insert('test.cp_ea_employee_achievments', $data, [
            'receive_date' => ParamType::DATE,
            'add_date'     => ParamType::DATE,
        ]);
    }
}
