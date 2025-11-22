<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\Entity;

use Database\ORM\Attribute\Column;
use Database\ORM\Attribute\Entity;

/**
 * Должность (не привязаны сотрудники)
 */
#[Entity('test.cp_response')]
class Post
{
    public function __construct(
        #[Column] private int $id,
        #[Column] private string $name,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function toArray(): array
    {
        return [
            'id'   => $this->id,
            'name' => $this->name,
        ];
    }
}
