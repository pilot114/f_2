<?php

declare(strict_types=1);

namespace App\Domain\CallCenter\UseDesk\Controller;

use App\Common\Attribute\CpMenu;
use App\Common\Attribute\RpcMethod;
use App\Common\Attribute\RpcParam;
use App\Domain\CallCenter\UseDesk\DTO\GetChatsResponse;
use App\Domain\CallCenter\UseDesk\UseCase\GetChatsUseCase;

class GetChatsController
{
    public function __construct(
        private GetChatsUseCase $useCase
    ) {
    }

    #[RpcMethod(
        'callCenter.useDesk.getChats',
        'получить список чатов',
        examples: [
            [
                'summary' => 'получить список чатов',
                'params'  => [
                    'markedOnly' => true,
                    'noAnswer'   => true,
                ],
            ],
        ],
    )]
    #[CpMenu('callcenter/call-center-bot')]
    public function __invoke(
        #[RpcParam('Только отмеченные чаты')]
        bool $markedOnly = false,
        #[RpcParam('Только чаты без ответа')]
        bool $noAnswer = false
    ): GetChatsResponse {
        $chats = $this->useCase->getChats($markedOnly, $noAnswer);

        return GetChatsResponse::build($chats);
    }
}
