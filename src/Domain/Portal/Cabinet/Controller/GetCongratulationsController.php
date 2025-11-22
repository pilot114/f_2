<?php

declare(strict_types=1);

namespace App\Domain\Portal\Cabinet\Controller;

use App\Common\Attribute\RpcMethod;
use App\Common\Attribute\RpcParam;
use App\Domain\Portal\Cabinet\DTO\GetCongratulationsResponse;
use App\Domain\Portal\Cabinet\UseCase\GetCongratulationsUseCase;
use App\Domain\Portal\Security\Entity\SecurityUser;
use DateTimeImmutable;

class GetCongratulationsController
{
    public function __construct(
        private GetCongratulationsUseCase $useCase,
        private SecurityUser $currentUser,
    ) {
    }

    #[RpcMethod(
        'portal.cabinet.getCongratulations',
        'получить список поздравлений пользователя',
        examples: [
            [
                'summary' => 'получить список поздравлений пользователя',
                'params'  => [
                    'startFrom' => '2024-10-10',
                ],
            ],
        ],
    )]
    public function __invoke(
        #[RpcParam('Начало отсчета')]
        ?DateTimeImmutable $startFrom = null,
    ): GetCongratulationsResponse {

        $congratulations = $this->useCase->getCongratulations($this->currentUser->id, $startFrom);

        return GetCongratulationsResponse::build($congratulations);
    }
}
