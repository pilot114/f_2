<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\Controller;

use App\Common\Attribute\CpAction;
use App\Common\Attribute\RpcMethod;
use App\Domain\Events\Rewards\DTO\GetGroupRequest;
use App\Domain\Events\Rewards\DTO\GroupListResponse;
use App\Domain\Events\Rewards\UseCase\GetAvailableGroupsUseCase;
use App\Domain\Events\Rewards\UseCase\GetGroupsUseCase;

class GroupListController
{
    public function __construct(
        private GetGroupsUseCase   $getGroupsUseCase,
        private GetAvailableGroupsUseCase $getAvailableGroupsUseCase
    ) {
    }

    #[RpcMethod(
        'events.rewards.getGroups',
        'Список групп',
        examples: [
            [
                'summary' => 'Фильтр всем странам',
                'params'  => [
                    'country'     => 'Q_ANY',
                    'search'      => 'Название программы, номинации, категории',
                    'status'      => false,
                    'rewardTypes' => [
                        [
                            'id'   => 4,
                            'name' => 'Прочее',
                        ],
                    ],
                ],
            ],
        ],
        isAutomapped: true
    )]
    #[CpAction('awards_directory.read')]
    public function getGroups(
        GetGroupRequest $request
    ): GroupListResponse {
        //@TODO при большом количестве объектов падаем по памяти
        ini_set('memory_limit', -1);
        $data = $this->getGroupsUseCase->getGroups(
            $request->country,
            $request->search,
            $request->status,
            $request->rewardTypes
        );

        return GroupListResponse::build($data);
    }

    #[RpcMethod(
        'events.rewards.getAvailableGroups',
        'Список доступных групп для перемещения/создания программ',
        examples: [
            [
                'summary' => 'Список доступных групп для перемещения/создания программ',
                'params'  => [],
            ],
        ],
    )]
    #[CpAction('awards_directory.read')]
    public function getAvailableGroups(): GroupListResponse
    {
        $data = $this->getAvailableGroupsUseCase->getAvailableGroups();

        return GroupListResponse::build($data);
    }
}
