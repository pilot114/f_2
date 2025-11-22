<?php

declare(strict_types=1);

namespace App\Domain\Analytics\Mcp\Entity;

use Database\ORM\Attribute\Column;
use Database\ORM\Attribute\Entity;

#[Entity('test.cp_artefact_link', sequenceName: 'test.cp_artefact_link_sq')]
class ArtefactLink
{
    public function __construct(
        #[Column(name: 'id')]
        protected int $id,
        #[Column(name: 'artefact')] // from OR to COLUMN
        protected Artefact $artefact,
    ) {
    }

    public function toArray(): array
    {
        return [
            'id'       => $this->id,
            'artefact' => $this->artefact->toArray(),
        ];
    }
}
