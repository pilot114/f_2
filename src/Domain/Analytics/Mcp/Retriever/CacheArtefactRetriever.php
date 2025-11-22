<?php

declare(strict_types=1);

namespace App\Domain\Analytics\Mcp\Retriever;

use App\Domain\Analytics\Mcp\Entity\Artefact;
use App\Domain\Analytics\Mcp\Enum\ArtefactType;
use Database\Connection\CpConnection;
use Database\ORM\DataMapper;
use Database\ORM\QueryRepository;

class CacheArtefactRetriever implements ArtefactRetrieverInterface
{
    /**
     * @var QueryRepository<Artefact>
     */
    private QueryRepository $queryRepository;

    public function __construct(
        private CpConnection $conn,
    ) {
        // @phpstan-ignore-next-line
        $this->queryRepository = new QueryRepository($conn, new DataMapper(), Artefact::class);
    }

    /**
     * @return string[]
     */
    public function getNameList(ArtefactType $type): array
    {
        $sql = "SELECT name
        FROM test.cp_artefact
        WHERE type = :type
        ORDER by name";

        $raw = $this->conn->query($sql , [
            'type' => $type->value,
        ]);
        return array_column(iterator_to_array($raw), 'name');
    }

    public function getChunk(array $fullNames, ArtefactType $type): array
    {
        // TODO
        return [];
    }

    public function get(string $fullName, ArtefactType $type): ?Artefact
    {
        return $this->queryRepository
            ->findOneBy([
                'name' => mb_strtolower($fullName),
                'type' => $type->value,
            ])
        ;
    }

    /**
     * @return array<array{name: string, type: string}>
     */
    public function search(string $q, ?ArtefactType $type = null): array
    {
        $sql = "SELECT name, type
        FROM test.cp_artefact
        WHERE name LIKE :q";
        if ($type instanceof ArtefactType) {
            $sql .= " AND type = :type";
        }

        $params = [
            'q' => "%" . mb_strtolower($q) . "%",
        ];
        if ($type instanceof ArtefactType) {
            $params['type'] = $type->value;
        }

        $items = $this->conn->query($sql, $params);
        return array_map(static fn (array $x): array => [
            'name' => $x['name'],
            'type' => $x['type'],
        ], iterator_to_array($items));
    }
}
