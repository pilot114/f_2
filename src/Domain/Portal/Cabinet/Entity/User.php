<?php

declare(strict_types=1);

namespace App\Domain\Portal\Cabinet\Entity;

use Database\ORM\Attribute\Column;
use Database\ORM\Attribute\Entity;

#[Entity(name: 'TEST.CP_EMP')]
class User
{
    public function __construct(
        #[Column] public readonly int $id,
        #[Column] public readonly string $name,
        #[Column] public readonly string $email,
        #[Column(collectionOf: Response::class)] private array $responses = [],
    ) {
    }

    public function getResponse(): ?Response
    {
        $responses = array_values($this->responses);

        return $responses[0] ?? null;
    }

    public function toArray(): array
    {
        return [
            'id'       => $this->id,
            'name'     => $this->name,
            'email'    => $this->email,
            'response' => $this->getResponse()?->toArray(),
        ];
    }
}
