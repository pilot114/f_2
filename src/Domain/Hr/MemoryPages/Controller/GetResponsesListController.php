<?php

declare(strict_types=1);

namespace App\Domain\Hr\MemoryPages\Controller;

use App\Common\Attribute\RpcMethod;
use App\Domain\Hr\MemoryPages\DTO\GetResponsesListResponse;
use App\Domain\Hr\MemoryPages\UseCase\GetResponsesListUseCase;

class GetResponsesListController
{
    public function __construct(
        private GetResponsesListUseCase $useCase
    ) {
    }

    #[RpcMethod(
        'hr.memoryPages.getResponsesList',
        'Список должностей',
        examples: [
            [
                'summary' => 'Список должностей',
                'params'  => [],
            ],
        ],
    )]
    public function __invoke(): GetResponsesListResponse
    {
        $list = $this->useCase->getList();

        return GetResponsesListResponse::build($list);
    }
}
