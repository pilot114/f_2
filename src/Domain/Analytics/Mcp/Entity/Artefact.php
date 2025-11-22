<?php

declare(strict_types=1);

namespace App\Domain\Analytics\Mcp\Entity;

use App\Domain\Analytics\Mcp\Enum\ArtefactType;
use Database\ORM\Attribute\Column;
use Database\ORM\Attribute\Entity;

#[Entity('test.cp_artefact', sequenceName: 'test.cp_artefact_sq')]
class Artefact
{
    public function __construct(
        #[Column(name: 'id')]
        protected int $id,
        #[Column(name: 'name')]
        protected string $name,
        #[Column(name: 'type')]
        protected ArtefactType $type,
        #[Column(name: 'content')]
        protected string $content, // serialized data
        //        #[Column(name: 'down_links')]
        //        protected array $downLinks = [],
        //        #[Column(name: 'up_links')]
        //        protected array $upLinks = [],
    ) {
    }

    public function setContent(string $serializedContent): void
    {
        $this->content = $serializedContent;
    }

    public function getContent(): mixed
    {
        return unserialize($this->content);
    }

    public function hideContent(): void
    {
        $this->content = '***HIDDEN***';
    }

    public function toArray(): array
    {
        return [
            'id'      => $this->id,
            'name'    => $this->name,
            'type'    => $this->type,
            'content' => $this->getContent(),
            //            'downLinks' => array_map(static fn (ArtefactLink $link): array => $link->toArray(), $this->downLinks),
            //            'upLinks'   => array_map(static fn (ArtefactLink $link): array => $link->toArray(), $this->upLinks),
        ];
    }
}
