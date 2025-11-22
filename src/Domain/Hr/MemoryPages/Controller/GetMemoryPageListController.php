<?php

declare(strict_types=1);

namespace App\Domain\Hr\MemoryPages\Controller;

use App\Common\Attribute\RpcMethod;
use App\Domain\Hr\MemoryPages\DTO\GetMemoryPageListRequest;
use App\Domain\Hr\MemoryPages\DTO\GetMemoryPageListResponse;
use App\Domain\Hr\MemoryPages\UseCase\GetMemoryPageListUseCase;

class GetMemoryPageListController
{
    public function __construct(
        private GetMemoryPageListUseCase $useCase,
    ) {
    }

    #[RpcMethod(
        'hr.memoryPages.getMemoryPageList',
        'Список страниц памяти',
        examples: [
            [
                'summary' => 'Список страниц памяти',
                'params'  => [
                    'search' => 'ФИО или должность',
                ],
            ],
        ],
        isAutomapped: true
    )]
    public function __invoke(GetMemoryPageListRequest $request): GetMemoryPageListResponse
    {
        $list = $this->useCase->getList($request);

        return GetMemoryPageListResponse::build($list);
    }
}
