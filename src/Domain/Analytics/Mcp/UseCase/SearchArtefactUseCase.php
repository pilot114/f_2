<?php

declare(strict_types=1);

namespace App\Domain\Analytics\Mcp\UseCase;

use App\Domain\Analytics\Mcp\Enum\ArtefactType;
use App\Domain\Analytics\Mcp\Retriever\CacheArtefactRetriever;

class SearchArtefactUseCase
{
    public function __construct(
        private CacheArtefactRetriever $retriever,
    ) {
    }

    /**
     * @return array{
     *     items: array<array{name: string, type: string}>,
     *     total: int
     * }
     */
    public function search(string $query, ?ArtefactType $type = null): array
    {
        $objects = $this->retriever->search($query, $type);

        return [
            'items' => $objects,
            'total' => count($objects),
        ];
    }
}
