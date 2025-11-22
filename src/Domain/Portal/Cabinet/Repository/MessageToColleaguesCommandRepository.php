<?php

declare(strict_types=1);

namespace App\Domain\Portal\Cabinet\Repository;

use App\Domain\Portal\Cabinet\Entity\MessageToColleagues;
use Database\ORM\CommandRepository;

/**
 * @extends CommandRepository<MessageToColleagues>
 */
class MessageToColleaguesCommandRepository extends CommandRepository
{
    protected string $entityName = MessageToColleagues::class;
}
