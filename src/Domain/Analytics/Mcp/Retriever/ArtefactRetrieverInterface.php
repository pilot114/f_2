<?php

declare(strict_types=1);

namespace App\Domain\Analytics\Mcp\Retriever;

use App\Domain\Analytics\Mcp\Enum\ArtefactType;

interface ArtefactRetrieverInterface
{
    // получение всех имен артефактов определенного типа
    public function getNameList(ArtefactType $type): array;
    // получение артефакта по имени
    public function get(string $fullName, ArtefactType $type): ?object;
    // получение артефакта по имени (оптимизация)
    public function getChunk(array $fullNames, ArtefactType $type): array;
    // поиск артефактов по имени
    public function search(string $q, ?ArtefactType $type = null): array;
}
