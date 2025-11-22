<?php

declare(strict_types=1);

namespace App\Domain\Analytics\Mcp\UseCase;

use App\Domain\Analytics\Mcp\Entity\Artefact;
use App\Domain\Analytics\Mcp\Enum\ArtefactType;
use App\Domain\Analytics\Mcp\Retriever\CacheArtefactRetriever;
use App\Domain\Analytics\Mcp\Retriever\OracleArtefactRetriever;
use Database\Schema\DbObject\Link;
use Exception;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GetArtefactUseCase
{
    public const SIZE_LIMIT_KB = 100;
    public const MAX_DEPTH = 3;

    public function __construct(
        private CacheArtefactRetriever  $cacheRetriever,
        private OracleArtefactRetriever $oracleRetriever,
    ) {
    }

    /**
     * Обобщенный метод для обработки нескольких уровней вложенности
     */
    public function processNestedLinks(string $fullName, ArtefactType $type, bool $onlyCache): array
    {
        /** @var Artefact|null $artefact */
        $artefact = $this->cacheRetriever->get(mb_strtoupper($fullName), $type);

        if ($artefact === null) {
            throw new NotFoundHttpException("Не найдена сущность $fullName");
        }

        $result = [
            'artefact' => $artefact->toArray(),
            'depends'  => [],
        ];

        $prevLevelObjects = [$artefact];

        for ($depth = 1; $depth <= self::MAX_DEPTH; $depth++) {
            $currentLevelObjects = [];

            foreach ($prevLevelObjects as $object) {
                $unserialized = $object->getContent();
                if (!is_object($unserialized)) {
                    continue;
                }
                if (!property_exists($unserialized, 'links')) {
                    continue;
                }
                $links = $unserialized->links;

                $this->addLinksToResult($links, $currentLevelObjects, $result, $onlyCache, $depth);

                if ((strlen(serialize($result)) / 1024) > self::SIZE_LIMIT_KB) {
                    return $result;
                }
            }

            if (empty($currentLevelObjects)) {
                break;
            }

            $prevLevelObjects = $currentLevelObjects;
        }

        return $result;
    }

    private function addLinksToResult(array $links, array &$next, array &$result, bool $onlyCache, int $level): void
    {
        foreach ($links as $link) {
            /** @var Artefact $object */
            $object = $this->linkToDepend($link, $onlyCache);

            if ($object !== null && $object->getContent() !== null) {
                $next[] = clone $object;
                $object->hideContent();
                $result['depends'][] = [
                    'artefact' => $object,
                    'level'    => $level,
                ];
            }
        }
    }

    private function linkToDepend(Link|array $link, bool $onlyCache): ?object
    {
        if (is_array($link) && isset($link['table'])) {
            $type = ArtefactType::TABLE;
            $name = $link['table'];
        } elseif ($link instanceof Link) {
            $type = ArtefactType::fromDbObjectType($link->objectType);
            $name = $link->objectFullName;
        } else {
            return null;
        }

        // TODO: fix incorrect context prefix
        if (substr_count($name, '.') > 1 && $type === ArtefactType::TABLE) {
            [$a, $b, $c] = explode('.', $name);
            $name = "$a.$c";
        }
        $name = mb_strtoupper($name);

        $object = $this->cacheRetriever->get($name, $type);
        if (!$object instanceof Artefact) {
            if ($onlyCache) {
                return null;
            }
            try {
                $object = $this->oracleRetriever->get($name, $type);
            } catch (Exception $e) {
                // TODO: fix (Table not found: TEHNO.V_CP_DEPARTAMENT_FROM_SV)
                // ORA-01435: user does not exist
                return null;
            }
        }

        return $object;
    }
}
