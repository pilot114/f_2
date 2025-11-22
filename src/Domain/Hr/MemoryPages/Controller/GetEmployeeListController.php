<?php

declare(strict_types=1);

namespace App\Domain\Hr\MemoryPages\Controller;

use App\Common\Attribute\RpcMethod;
use App\Domain\Hr\MemoryPages\DTO\GetEmployeeListRequest;
use App\Domain\Hr\MemoryPages\DTO\GetEmployeeListResponse;
use App\Domain\Hr\MemoryPages\UseCase\GetEmployeeListUseCase;

class GetEmployeeListController
{
    public function __construct(
        private GetEmployeeListUseCase $useCase
    ) {
    }

    #[RpcMethod(
        'hr.memoryPages.getEmployeeList',
        'Список сотрудников для страниц памяти',
        examples: [
            [
                'summary' => 'Список сотрудников для страниц памяти',
                'params'  => [
                    'search' => 'Иванов Иван Иванович',
                ],
            ],
        ],
        isAutomapped: true
    )]
    public function __invoke(GetEmployeeListRequest $request): GetEmployeeListResponse
    {
        $list = $this->useCase->getList($request);

        return GetEmployeeListResponse::build($list);
    }
}
