<?php

declare(strict_types=1);

namespace App\Domain\Hr\MemoryPages\Controller;

use App\Common\Attribute\CpAction;
use App\Common\Attribute\RpcMethod;
use App\Common\Attribute\RpcParam;
use App\Domain\Hr\MemoryPages\UseCase\DeleteMemoryPageUseCase;

class DeleteMemoryPageController
{
    public function __construct(
        private DeleteMemoryPageUseCase $useCase
    ) {
    }

    #[RpcMethod(
        'hr.memoryPages.deleteMemoryPage',
        'удаление страницы памяти',
        examples: [
            [
                'summary' => 'удаление страницы памяти',
                'params'  => [
                    'memoryPageId' => 1,
                ],
            ],
        ],
    )]
    #[CpAction('memory_pages.memory_pages_add')]
    public function __invoke(
        #[RpcParam('id страницы памяти')] int $memoryPageId,
    ): void {
        $this->useCase->deleteMemoryPage($memoryPageId);
    }
}
