<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\Controller;

use App\Common\Attribute\CpAction;
use App\Common\Attribute\RpcMethod;
use App\Domain\Events\Rewards\DTO\SetPartnerStatusRequest;
use App\Domain\Events\Rewards\UseCase\SetPartnerStatusUseCase;

class SetPartnerStatusController
{
    public function __construct(
        private SetPartnerStatusUseCase $setPartnerStatusUseCase,
    ) {
    }

    #[RpcMethod(
        'events.rewards.setPartnerStatus',
        'Изменить статус партнёра в режиме по Событию',
        examples: [
            [
                'summary' => 'Изменить статус партнёра в режиме по Событию',
                'params'  => [
                    'partnerId'            => 1234,
                    'partnerStatus'        => 1,
                    'eventId'              => 1,
                    'rewardIssuanceStates' => [
                        [
                            'id'      => 1233,
                            'status'  => 3,
                            'comment' => 'комментарий',
                        ],
                    ],
                ],
            ],
        ],
        isAutomapped: true
    )]
    #[CpAction('awards_directory.edit')]
    public function __invoke(
        SetPartnerStatusRequest $request,
    ): void {
        $this->setPartnerStatusUseCase->setPartnerStatus($request);
    }
}
