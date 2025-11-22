<?php

declare(strict_types=1);

namespace App\Domain\Portal\Cabinet\Controller;

use App\Common\Attribute\RpcMethod;
use App\Domain\Portal\Cabinet\UseCase\DeleteMessageToColleaguesUseCase;

class DeleteMessageToColleaguesController
{
    public function __construct(
        private DeleteMessageToColleaguesUseCase $useCase,
    ) {
    }

    #[RpcMethod(
        'portal.cabinet.deleteMessageToColleagues',
        'удалить сообщение коллегам текущего пользователя',
        examples: [
            [
                'summary' => 'удалить сообщение коллегам текущего пользователя',
                'params'  => [],
            ],
        ],
    )]
    public function __invoke(): bool
    {
        return $this->useCase->delete();
    }
}
