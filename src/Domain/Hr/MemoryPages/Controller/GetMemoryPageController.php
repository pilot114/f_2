<?php

declare(strict_types=1);

namespace App\Domain\Hr\MemoryPages\Controller;

use App\Common\Attribute\RpcMethod;
use App\Domain\Hr\MemoryPages\DTO\GetMemoryPageRequest;
use App\Domain\Hr\MemoryPages\DTO\GetMemoryPageResponse;
use App\Domain\Hr\MemoryPages\UseCase\GetMemoryPageUseCase;

class GetMemoryPageController
{
    public function __construct(
        private GetMemoryPageUseCase $useCase,
    ) {
    }

    #[RpcMethod(
        'hr.memoryPages.getMemoryPage',
        'Страница памяти',
        examples: [
            [
                'summary' => 'Страница памяти',
                'params'  => [
                    'id' => 1,
                ],
            ],
        ],
        isAutomapped: true
    )]
    public function __invoke(GetMemoryPageRequest $request): GetMemoryPageResponse
    {
        $memoryPage = $this->useCase->getItem($request);

        return GetMemoryPageResponse::build($memoryPage);
    }
}
