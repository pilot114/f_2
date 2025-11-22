<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\Controller;

use App\Common\Attribute\RpcMethod;
use App\Domain\Events\Rewards\DTO\PartnerStatusesListResponse;
use App\Domain\Events\Rewards\UseCase\GetAvailablePartnerStatusesUseCase;

class PartnerStatusesListController
{
    public function __construct(
        private GetAvailablePartnerStatusesUseCase $useCase
    ) {
    }

    #[RpcMethod(
        'events.rewards.getAvailablePartnerStatuses',
        'Список доступных статусов партнёров',
        examples: [
            [
                'summary' => 'Список доступных статусов партнёров',
                'params'  => [],
            ],
        ],
    )]

    public function getAvailablePartnerStatuses(): PartnerStatusesListResponse
    {
        $statuses = $this->useCase->getList();

        return new PartnerStatusesListResponse(
            items: $statuses->values()->all()
        );
    }
}
