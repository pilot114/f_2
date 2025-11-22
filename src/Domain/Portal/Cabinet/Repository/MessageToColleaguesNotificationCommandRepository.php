<?php

declare(strict_types=1);

namespace App\Domain\Portal\Cabinet\Repository;

use App\Domain\Portal\Cabinet\Entity\MessageToColleaguesNotification;
use Database\ORM\CommandRepository;

/**
 * @extends CommandRepository<MessageToColleaguesNotification>
 */
class MessageToColleaguesNotificationCommandRepository extends CommandRepository
{
    protected string $entityName = MessageToColleaguesNotification::class;

    public function deleteNotifications(int $messageId): void
    {
        $this->conn->delete(
            MessageToColleaguesNotification::TABLE,
            [
                'MESSAGE_ID' => $messageId,
            ]
        );
    }
}
