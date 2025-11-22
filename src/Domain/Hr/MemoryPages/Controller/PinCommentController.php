<?php

declare(strict_types=1);

namespace App\Domain\Hr\MemoryPages\Controller;

use App\Common\Attribute\CpAction;
use App\Common\Attribute\RpcMethod;
use App\Domain\Hr\MemoryPages\DTO\PinCommentRequest;
use App\Domain\Hr\MemoryPages\UseCase\PinCommentUseCase;

class PinCommentController
{
    public function __construct(
        private PinCommentUseCase $useCase
    ) {
    }

    #[RpcMethod(
        'hr.memoryPages.pinComment',
        'закрепить/открепить комментарий',
        examples: [
            [
                'summary' => 'закрепить/открепить комментарий',
                'params'  => [
                    'commentId' => 1,
                    'isPinned'  => true,
                ],
            ],
        ],
        isAutomapped: true
    )]
    #[CpAction('memory_pages.memory_pages_add')]
    /**
     * @return array{
     *     id: int,
     *     employee: array{
     *          id: int,
     *          name: string,
     *          response: array{
     *              id: int,
     *              name: string,
     *          },
     *          avatar: array{
     *              original: string,
     *              small: string,
     *              medium: string,
     *              large: string,
     *          },
     *      },
     *     text: string,
     *     isPinned: bool,
     *     createDate: string,
     *     photos: array<array{
     *        original: string,
     *        small: string,
     *        medium: string,
     *        large: string,
     *    }>
     * }
     */
    public function __invoke(PinCommentRequest $request): array
    {
        $comment = $this->useCase->togglePinned($request);

        return $comment->toArray();
    }
}
