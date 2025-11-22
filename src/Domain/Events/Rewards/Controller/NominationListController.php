<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\Controller;

use App\Common\Attribute\CpAction;
use App\Common\Attribute\RpcMethod;
use App\Common\Attribute\RpcParam;
use App\Domain\Events\Rewards\DTO\NominationsForVerificationFilterResponse;
use App\Domain\Events\Rewards\UseCase\GetNominationsForVerificationFilterUseCase;

class NominationListController
{
    public function __construct(
        private GetNominationsForVerificationFilterUseCase $useCase
    ) {
    }

    #[RpcMethod(
        'events.rewards.getNominationsForVerificationFilter',
        'Список номинаций для фильтра',
        examples: [
            [
                'summary' => 'Список номинаций для фильтра',
                'params'  => [
                    'programIds' => [1, 2],
                ],
            ],
        ],
    )]
    #[CpAction('awards_directory.read')]
    public function getNominationsForVerificationFilter(
        #[RpcParam('id программ')]
        array $programIds,
    ): NominationsForVerificationFilterResponse {
        $nominations = $this->useCase->getNominationsForVerificationFilter($programIds);

        return NominationsForVerificationFilterResponse::build($nominations);
    }
}
