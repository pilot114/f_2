<?php

declare(strict_types=1);

namespace App\Domain\Hr\MemoryPages\Entity;

use App\Domain\Portal\Files\Entity\File;
use Database\ORM\Attribute\Column;
use Database\ORM\Attribute\Entity;

#[Entity(name: 'TEST.CP_EMP')]
class Employee
{
    private ?File $avatar = null;

    public function __construct(
        #[Column] public readonly int $id,
        #[Column] public readonly string $name,
        #[Column(collectionOf: Response::class)] private array $response = [],
    ) {
    }

    public function getResponse(): ?Response
    {
        $responses = array_values($this->response);

        return $responses[0] ?? null;
    }

    public function setAvatar(File $avatar): void
    {
        $this->avatar = $avatar;
    }

    public function toArray(): array
    {
        return [
            'id'       => $this->id,
            'name'     => $this->name,
            'response' => $this->getResponse()?->toArray(),
            'avatar'   => $this->avatar?->getImageUrls(),
        ];
    }
}
