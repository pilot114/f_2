<?php

declare(strict_types=1);

namespace App\Domain\Analytics\Mcp\Controller;

use App\Common\Attribute\RpcMethod;
use App\Common\Attribute\RpcParam;
use App\Domain\Analytics\Mcp\Enum\ArtefactType;
use App\Domain\Analytics\Mcp\UseCase\GetArtefactUseCase;
use PhpMcp\Server\Attributes\McpTool;

#[McpTool(
    name: 'get-artefact',
    description: 'Получение объекта в БД'
)]
readonly class GetArtefactController
{
    public function __construct(
        private GetArtefactUseCase $useCase,
    ) {
    }

    #[RpcMethod(
        'analytics.mcp.getArtefact',
        'Получение объекта в БД',
        examples: [
            [
                'summary' => 'Получение объекта в БД',
                'params'  => [
                    'fullName' => 'ANALYSIS.PKG_CP.CALC_RGN_MNG_KPI',
                    'type'     => 'PROCEDURE',
                ],
            ],
        ]
    )]
    /**
     * @return array{
     *     artefact: array<string>,
     *     depends: array<string>,
     * }
     */
    public function __invoke(
        #[RpcParam('полное имя объекта')]
        string $fullName,
        #[RpcParam('тип артефакта')]
        ArtefactType $type,
        #[RpcParam('искать только в кэше')]
        bool $onlyCache = true,
    ): array {
        return $this->useCase->processNestedLinks($fullName, $type, $onlyCache);
    }
}
