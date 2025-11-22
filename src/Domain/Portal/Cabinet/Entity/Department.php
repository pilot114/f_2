<?php

declare(strict_types=1);

namespace App\Domain\Portal\Cabinet\Entity;

use Database\ORM\Attribute\Column;
use Database\ORM\Attribute\Entity;

#[Entity(name: 'test.cp_departament')]
class Department
{
    public const TOP_LEVEL_DEPARTMENT_ID = 1;
    private ?Department $child = null;

    public function __construct(
        #[Column] private int $id,
        #[Column] private string $name,
        #[Column(name: 'parent_id')] private int $parentId,
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getParentId(): int
    {
        return $this->parentId;
    }

    public function isTopLevel(): bool
    {
        return $this->parentId === self::TOP_LEVEL_DEPARTMENT_ID;
    }

    public function getChild(): ?Department
    {
        return $this->child;
    }

    public function addChild(?Department $child): void
    {
        $this->child = $child;
    }

    public function toArray(): array
    {
        return [
            'id'    => $this->id,
            'name'  => $this->getName(),
            'child' => $this->child?->toArray(),
        ];
    }
}
