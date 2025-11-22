<?php

declare(strict_types=1);

namespace App\Domain\CallCenter\UseDesk\Repository;

use App\Domain\CallCenter\UseDesk\Entity\MarkedChat;
use Database\ORM\CommandRepository;

/** @extends CommandRepository<MarkedChat> */
class MarkChatCommandRepository extends CommandRepository
{
    protected string $entityName = MarkedChat::class;

    public function markChat(MarkedChat $markedChat): MarkedChat
    {
        $this->create($markedChat);

        return $markedChat;
    }

    public function unmarkChat(MarkedChat $markedChat): bool
    {
        return $this->delete($markedChat->getId());
    }
}
