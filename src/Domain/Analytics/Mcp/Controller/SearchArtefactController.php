<?php

declare(strict_types=1);

namespace App\Domain\Analytics\Mcp\Controller;

use App\Common\Attribute\RpcMethod;
use App\Common\Attribute\RpcParam;
use App\Domain\Analytics\Mcp\Enum\ArtefactType;
use App\Domain\Analytics\Mcp\UseCase\SearchArtefactUseCase;
use PhpMcp\Server\Attributes\McpTool;
use Symfony\Component\Validator\Constraints as Assert;

#[McpTool(
    name: 'search-artefact',
    description: 'Поиск объектов в БД по имени и типу'
)]
readonly class SearchArtefactController
{
    public function __construct(
        private SearchArtefactUseCase $useCase,
    ) {
    }

    #[RpcMethod(
        'analytics.mcp.searchArtefact',
        'Поиск объектов в БД по имени и типу',
        examples: [
            [
                'summary' => 'Поиск объектов в БД по имени и типу',
                'params'  => [
                    'q'    => 'MNG_KPI',
                    'type' => 'PROCEDURE',
                ],
            ],
        ]
    )]
    /**
     * @return array{
     *     items: array<array{name: string, type: string}>,
     *     total: int
     * }
     */
    public function __invoke(
        #[RpcParam('поиск')]
        #[Assert\Length(
            min: 3,
            minMessage: 'Длина строки поиска должна быть не менее {{ limit }} символов',
        )]
        string $q,
        #[RpcParam('тип артефакта')]
        ?ArtefactType $type = null,
    ): array {
        return $this->useCase->search($q, $type);
    }
}
