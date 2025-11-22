<?php

declare(strict_types=1);

namespace App\Domain\Portal\Cabinet\DTO;

use App\Domain\Portal\Cabinet\Entity\MessageToColleagues;
use App\Domain\Portal\Cabinet\Entity\User;
use DateTimeImmutable;

readonly class MessageToColleaguesResponse
{
    public function __construct(
        public int $id,
        public string $message,
        public DateTimeImmutable $startDate,
        public DateTimeImmutable $endDate,
        public DateTimeImmutable $changeDate,
        public int $userId,
        public array $notify
    ) {
    }

    public static function build(MessageToColleagues $message): self
    {
        return new self(
            $message->getId(),
            $message->getMessage(),
            $message->getStartDate(),
            $message->getEndDate(),
            $message->getChangeDate(),
            $message->user->id,
            array_map(fn (User $user): array => $user->toArray(),$message->getNotificationUsers())
        );
    }
}
