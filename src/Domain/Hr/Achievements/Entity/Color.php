<?php

declare(strict_types=1);

namespace App\Domain\Hr\Achievements\Entity;

use App\Domain\Hr\Achievements\DTO\ColorResponse;
use Database\ORM\Attribute\{Column, Entity};

#[Entity(name: 'TEST.CP_EA_COLORS', sequenceName: 'TEST.CP_EA_COLORS_SQ')]
class Color
{
    public function __construct(
        #[Column(name: 'id')] public int $id,
        #[Column(name: 'url')] private string $url,
        #[Column(name: 'file_id')] private int $fileId,
    ) {
    }

    public function getFileId(): int
    {
        return $this->fileId;
    }

    public function setFile(int $fileId, string $url): self
    {
        $this->fileId = $fileId;
        $this->url = $url;
        return $this;
    }

    public function toColorResponse(): ColorResponse
    {
        return new ColorResponse(
            id: $this->id,
            url: $this->url,
            fileId: $this->fileId,
        );
    }
}
