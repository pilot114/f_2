<?php

declare(strict_types=1);

namespace App\Domain\Hr\Achievements\Entity;

use App\Common\Service\File\FileService;
use App\Domain\Hr\Achievements\DTO\ImageResponse;
use Database\ORM\Attribute\Column;
use Database\ORM\Attribute\Entity;

#[Entity(name: 'TEST.CP_EA_IMAGE_LIBRARY', sequenceName: 'TEST.CP_EA_IMAGE_LIBRARY_SQ')]
readonly class Image
{
    public function __construct(
        #[Column(name: 'id')] public int           $id,
        #[Column(name: 'cp_files_id')] private int $fileId,
        #[Column(name: 'name')] private string     $name,
    ) {
    }

    public function toImageResponse(): ImageResponse
    {
        return new ImageResponse(
            id: $this->id,
            name: $this->name,
            url: FileService::getCpFileUrlView($this->fileId),
        );
    }
}
