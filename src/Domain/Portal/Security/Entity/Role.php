<?php

declare(strict_types=1);

namespace App\Domain\Portal\Security\Entity;

use Database\ORM\Attribute\Column;
use Database\ORM\Attribute\Entity;

#[Entity('acl.roles')]
readonly class Role
{
    public function __construct(
        #[Column] public int $id,
        #[Column] private string $name,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }
}
