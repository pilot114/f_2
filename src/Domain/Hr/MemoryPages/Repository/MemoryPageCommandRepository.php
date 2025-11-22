<?php

declare(strict_types=1);

namespace App\Domain\Hr\MemoryPages\Repository;

use App\Domain\Hr\MemoryPages\Entity\MemoryPage;
use Database\ORM\CommandRepository;

/**
 * @extends CommandRepository<MemoryPage>
 */
class MemoryPageCommandRepository extends CommandRepository
{
    protected string $entityName = MemoryPage::class;
}
