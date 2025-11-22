<?php

declare(strict_types=1);

namespace App\Domain\Portal\Cabinet\Controller;

use App\Common\Attribute\RpcMethod;
use App\Domain\Portal\Cabinet\DTO\AddMessageToColleaguesRequest;
use App\Domain\Portal\Cabinet\DTO\MessageToColleaguesResponse;
use App\Domain\Portal\Cabinet\UseCase\AddMessageToColleaguesUseCase;

class AddMessageToColleaguesController
{
    public function __construct(
        private AddMessageToColleaguesUseCase $useCase,
    ) {
    }

    #[RpcMethod(
        'portal.cabinet.addMessageToColleagues',
        'добавить сообщение коллегам',
        examples: [
            [
                'summary' => 'добавить сообщение коллегам',
                'params'  => [
                    'startDate'     => '2025-06-02T15:52:01+07:00',
                    'endDate'       => '2025-06-05T15:52:01+07:00',
                    'message'       => 'текст сообщения',
                    'notifyUserIds' => [
                        9999,
                    ],
                ],
            ],
        ],
        isAutomapped: true
    )]
    public function __invoke(AddMessageToColleaguesRequest $request): MessageToColleaguesResponse
    {
        $message = $this->useCase->add($request);

        return MessageToColleaguesResponse::build($message);
    }
}
