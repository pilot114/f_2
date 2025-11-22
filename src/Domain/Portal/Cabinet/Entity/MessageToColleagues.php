<?php

declare(strict_types=1);

namespace App\Domain\Portal\Cabinet\Entity;

use Database\ORM\Attribute\Column;
use Database\ORM\Attribute\Entity;
use DateTimeImmutable;

#[Entity(name: self::TABLE, sequenceName: self::SEQUENCE)]
class MessageToColleagues
{
    public const SEQUENCE = 'TEST.CP_EMP_MESSAGES_SQ';
    public const TABLE = 'TEST.CP_EMP_MESSAGES';

    public function __construct(
        #[Column] private int                                            $id,
        #[Column(name: 'cp_emp')] public readonly User                   $user,
        #[Column] private string                                         $message,
        #[Column(name: 'message_start_date')] private DateTimeImmutable  $startDate,
        #[Column(name: 'message_end_date')] private DateTimeImmutable    $endDate,
        #[Column(name: 'message_change_date')] private DateTimeImmutable $changeDate,
        /** @var MessageToColleaguesNotification[] $notifications */
        private array                                                    $notifications = [],
    ) {
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    public function getEndDate(): DateTimeImmutable
    {
        return $this->endDate;
    }

    public function isActual(): bool
    {
        return $this->endDate > new DateTimeImmutable();
    }

    public function isInFuture(): bool
    {
        return $this->startDate > new DateTimeImmutable();
    }

    public function isActive(): bool
    {
        $now = new DateTimeImmutable();
        return $this->endDate >= $now && $this->startDate <= $now;
    }

    public function getStartDate(): DateTimeImmutable
    {
        return $this->startDate;
    }

    public function edit(string $messageText, DateTimeImmutable $startDate, DateTimeImmutable $endDate): void
    {
        $this->message = $messageText;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->changeDate = new DateTimeImmutable();
    }

    public function getChangeDate(): DateTimeImmutable
    {
        return $this->changeDate;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function addNotification(MessageToColleaguesNotification $notification): void
    {
        $this->notifications[] = $notification;
    }

    /** @return string[] */
    public function getNotificationEmailsList(): array
    {
        $list = [];
        foreach ($this->notifications as $notification) {
            $list[] = $notification->user->email;
        }

        return $list;
    }

    /** @return User[] */
    public function getNotificationUsers(): array
    {
        $list = [];
        foreach ($this->notifications as $notification) {
            $list[] = $notification->user;
        }

        return $list;
    }
}
