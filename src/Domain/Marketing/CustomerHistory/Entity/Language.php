<?php

declare(strict_types=1);

namespace App\Domain\Marketing\CustomerHistory\Entity;

use Database\ORM\Attribute\Column;
use Database\ORM\Attribute\Entity;

#[Entity(name: Language::TABLE)]
class Language
{
    public const TABLE = 'test.ml_langs';

    public function __construct(
        #[Column(name: 'id')] public readonly string $id,
        #[Column(name: 'name')] public string $name,
    ) {
        $this->name = mb_ucfirst($name);
    }
}
