<?php

declare(strict_types=1);

namespace App\Domain\Portal\Cabinet\Entity;

use Database\ORM\Attribute\Column;
use Database\ORM\Attribute\Entity;

#[Entity(name: self::TABLE, sequenceName: self::SEQUENCE)]
class MessageToColleaguesNotification
{
    public const SEQUENCE = 'TEST.CP_EMP_MESSAGE_USERS_SQ';
    public const TABLE = 'TEST.CP_EMP_MESSAGE_USERS';

    public function __construct(
        #[Column] private int                              $id,
        #[Column(name: 'message_id')] public readonly int  $messageId,
        #[Column(name: 'cp_emp_to')] public readonly User $user,
    ) {
    }
}
