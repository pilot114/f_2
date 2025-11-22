<?php

declare(strict_types=1);

namespace App\Domain\Portal\Cabinet\Controller;

use App\Common\Attribute\RpcMethod;
use App\Domain\Portal\Cabinet\DTO\GetUsersListRequest;
use App\Domain\Portal\Cabinet\DTO\GetUsersListResponse;
use App\Domain\Portal\Cabinet\UseCase\GetUsersListUseCase;

class GetUsersListController
{
    public function __construct(
        private GetUsersListUseCase $useCase
    ) {
    }

    #[RpcMethod(
        'portal.cabinet.getUsersList',
        'Список пользователей для рассылки сообщений',
        examples: [
            [
                'summary' => 'Список пользователей для рассылки сообщений',
                'params'  => [
                    'search' => 'Иванов Иван Иванович',
                ],
            ],
        ],
        isAutomapped: true
    )]
    public function __invoke(GetUsersListRequest $request): GetUsersListResponse
    {
        $list = $this->useCase->getList($request);

        return GetUsersListResponse::build($list);
    }
}
