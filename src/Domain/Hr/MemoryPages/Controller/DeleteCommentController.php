<?php

declare(strict_types=1);

namespace App\Domain\Hr\MemoryPages\Controller;

use App\Common\Attribute\RpcMethod;
use App\Common\Attribute\RpcParam;
use App\Domain\Hr\MemoryPages\UseCase\DeleteCommentUseCase;

class DeleteCommentController
{
    public function __construct(
        private DeleteCommentUseCase $useCase
    ) {
    }

    #[RpcMethod(
        'hr.memoryPages.deleteComment',
        'удаление комментария',
        examples: [
            [
                'summary' => 'удаление комментария',
                'params'  => [
                    'commentId' => 1,
                ],
            ],
        ],
    )]
    public function __invoke(
        #[RpcParam('id комментария')] int $commentId,
    ): void {
        $this->useCase->deleteComment($commentId);
    }
}
