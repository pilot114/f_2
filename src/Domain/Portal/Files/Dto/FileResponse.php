<?php

declare(strict_types=1);

namespace App\Domain\Portal\Files\Dto;

class FileResponse
{
    public function __construct(
        public int $id,
        public string $name,
        public string $extension,
        public string $downloadUrl,
        public string $viewUrl,
        public string $collectionName,
        public int $idInCollection,
    ) {
    }
}
