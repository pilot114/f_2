<?php

declare(strict_types=1);

namespace App\Domain\Portal\Files\Repository;

use App\Domain\Portal\Files\Entity\File;
use Database\ORM\CommandRepository;

/**
 * @extends CommandRepository<File>
 */
class FileCommandRepository extends CommandRepository
{
    protected string $entityName = File::class;
}
