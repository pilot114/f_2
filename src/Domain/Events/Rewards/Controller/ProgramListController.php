<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\Controller;

use App\Common\Attribute\CpAction;
use App\Common\Attribute\RpcMethod;
use App\Domain\Events\Rewards\DTO\ProgramsForVerificationFilterResponse;
use App\Domain\Events\Rewards\UseCase\GetProgramsForVerificationFilterUseCase;

class ProgramListController
{
    public function __construct(
        private GetProgramsForVerificationFilterUseCase $useCase
    ) {
    }

    #[RpcMethod(
        'events.rewards.getProgramsForVerificationFilter',
        'Список доступных программ для фильтра',
        examples: [
            [
                'summary' => 'Список доступных программ для фильтра',
                'params'  => [
                ],
            ],
        ],
    )]
    #[CpAction('awards_directory.read')]
    public function getProgramsForVerificationFilter(
    ): ProgramsForVerificationFilterResponse {
        $programs = $this->useCase->getList();

        return ProgramsForVerificationFilterResponse::build($programs);
    }
}
