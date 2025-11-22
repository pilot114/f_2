<?php

declare(strict_types=1);

namespace App\Domain\Portal\Security\Entity;

use Database\ORM\Attribute\Column;
use Database\ORM\Attribute\Entity;

#[Entity('acl.v_access')]
class Permission
{
    public function __construct(
        #[Column] private string $id,
        #[Column] private int $type,
        #[Column] private string $name,
        #[Column] private string $access_type,
        #[Column] private ?int $resource_id,
        #[Column] private string $resource_type,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function __toString(): string
    {
        return sprintf(
            '(%s) %s %s | %s | %s %s',
            $this->id,
            $this->type,
            $this->name,
            $this->access_type,
            $this->resource_id,
            $this->resource_type,
        );
    }
}
