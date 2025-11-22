<?php

declare(strict_types=1);

namespace App\Domain\CallCenter\UseDesk\Entity;

use App\Domain\CallCenter\UseDesk\Enum\UserType;
use DateTimeImmutable;

class Message
{
    public function __construct(
        public readonly int $id,
        public readonly int $chatId,
        public readonly UserType $userType,
        public readonly string $text,
        public readonly DateTimeImmutable $date,
    ) {
    }

    public function isSendByUser(): bool
    {
        return $this->userType === UserType::EMPLOYEE;
    }

    public function toArray(): array
    {
        return [
            'id'       => $this->id,
            'userType' => $this->userType->value,
            'message'  => $this->text,
            'date'     => $this->date->format(DateTimeImmutable::ATOM),
        ];
    }
}
