<?php

declare(strict_types=1);

namespace App\Domain\Portal\Cabinet\Controller;

use App\Common\Attribute\RpcMethod;
use App\Domain\Portal\Cabinet\DTO\GetMessageToColleaguesRequest;
use App\Domain\Portal\Cabinet\DTO\MessageToColleaguesResponse;
use App\Domain\Portal\Cabinet\Entity\MessageToColleagues;
use App\Domain\Portal\Cabinet\UseCase\GetMessageToColleaguesUseCase;

class GetMessageToColleaguesController
{
    public function __construct(
        private GetMessageToColleaguesUseCase $useCase,
    ) {
    }

    #[RpcMethod(
        'portal.cabinet.getMessageToColleagues',
        'получить сообщение коллегам для пользователя',
        examples: [
            [
                'summary' => 'получить сообщение коллегам для пользователя',
                'params'  => [
                    'userId' => 9999,
                ],
            ],
        ],
        isAutomapped: true
    )]
    public function __invoke(GetMessageToColleaguesRequest $request): ?MessageToColleaguesResponse
    {
        $message = $this->useCase->get($request);

        return $message instanceof MessageToColleagues ? MessageToColleaguesResponse::build($message) : null;
    }
}
