<?php

declare(strict_types=1);

namespace App\Domain\CallCenter\UseDesk\Controller;

use App\Common\Attribute\CpMenu;
use App\Common\Attribute\RpcMethod;
use App\Common\Attribute\RpcParam;
use App\Domain\CallCenter\UseDesk\Entity\MarkedChat;
use App\Domain\CallCenter\UseDesk\UseCase\ToggleMarkChatUseCase;

class ToggleMarkChatController
{
    public function __construct(
        private ToggleMarkChatUseCase $useCase
    ) {
    }

    #[RpcMethod(
        'callCenter.useDesk.markChat',
        'отметить чат',
        examples: [
            [
                'summary' => 'отметить чат',
                'params'  => [
                    'chatId' => 1,
                ],
            ],
        ],
    )]
    #[CpMenu('callcenter/call-center-bot')]
    public function markChat(
        #[RpcParam('id чата, который надо отметить')]
        int $chatId,
    ): MarkedChat {
        return $this->useCase->markChat($chatId);

    }

    #[RpcMethod(
        'callCenter.useDesk.unmarkChat',
        'снять отметку с чата',
        examples: [
            [
                'summary' => 'снять отметку с чата',
                'params'  => [
                    'chatId' => 1,
                ],
            ],
        ],
    )]
    #[CpMenu('callcenter/call-center-bot')]
    public function unmarkChat(
        #[RpcParam('id чата, который надо отметить')]
        int $chatId,
    ): bool {
        return $this->useCase->unmarkChat($chatId);
    }
}
