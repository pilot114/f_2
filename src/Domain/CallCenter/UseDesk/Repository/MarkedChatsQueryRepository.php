<?php

declare(strict_types=1);

namespace App\Domain\CallCenter\UseDesk\Repository;

use App\Domain\CallCenter\UseDesk\Entity\MarkedChat;
use Database\ORM\QueryRepository;

/** @extends QueryRepository<MarkedChat> */
class MarkedChatsQueryRepository extends QueryRepository
{
    protected string $entityName = MarkedChat::class;
}
