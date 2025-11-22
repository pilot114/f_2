<?php

declare(strict_types=1);

namespace App\Domain\Dit\Gifts\Entity;

use Database\ORM\Attribute\Column;
use Database\ORM\Attribute\Entity;

#[Entity('tehno.SERTIFICAT_TYPE')]
class CertificateType
{
    public function __construct(
        #[Column(name: 'id')] public readonly int $id,
        #[Column(name: 'name')] public readonly string $name,
    ) {
    }

    public function toArray(): array
    {
        return [
            'id'   => $this->id,
            'name' => $this->name,
        ];
    }
}
