<?php

declare(strict_types=1);

namespace App\Domain\Portal\Files\Dto;

readonly class ChunkFileRequest
{
    public function __construct(
        public string $uploadId,
        public int $chunkIndex,
        public int $chunkCount,
        public string $fileName,
        public string $collection,
        public ?int $idInCollection = null,
    ) {
    }
}
