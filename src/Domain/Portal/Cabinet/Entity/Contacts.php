<?php

declare(strict_types=1);

namespace App\Domain\Portal\Cabinet\Entity;

use Database\ORM\Attribute\Column;
use Database\ORM\Attribute\Entity;

#[Entity(name: 'test.cp_emp')]
class Contacts
{
    public function __construct(
        #[Column] private string $email,
        #[Column] private ?string $telegram = null,
        #[Column] private ?string $phone = null,
    ) {
    }

    public function toArray(): array
    {
        return [
            'email'    => $this->getEmail(),
            'telegram' => $this->getTelegram(),
            'phone'    => $this->getPhone(),
        ];
    }

    public function getTelegram(): ?string
    {
        return $this->telegram;
    }

    public function setTelegram(?string $telegram): void
    {
        $this->telegram = $telegram;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): void
    {
        $this->phone = $phone;
    }

    public function getEmail(): string
    {
        return $this->email;
    }
}
