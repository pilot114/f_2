<?php

declare(strict_types=1);

namespace App\Domain\CallCenter\UseDesk\Entity;

use Database\ORM\Attribute\Column;
use Database\ORM\Attribute\Entity;
use DateTimeImmutable;

#[Entity(name: 'test.usedesk_marked_chats', sequenceName: 'test.usedesk_marked_chats_sq')]
class MarkedChat
{
    public function __construct(
        #[Column(name: 'id')] private int $id,
        #[Column(name: 'chat_id')] public readonly int $chatId,
        #[Column(name: 'mark_date')] public readonly DateTimeImmutable $markDate,
        #[Column(name: 'mark_user_id')] public readonly int $markUserId,
    ) {

    }

    public function getId(): int
    {
        return $this->id;
    }
}
