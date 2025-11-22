<?php

declare(strict_types=1);

namespace App\Domain\Hr\MemoryPages\Controller;

use App\Common\Attribute\RpcMethod;
use App\Domain\Hr\MemoryPages\DTO\EditCommentRequest;
use App\Domain\Hr\MemoryPages\UseCase\EditCommentUseCase;

class EditCommentController
{
    public function __construct(
        private EditCommentUseCase $useCase
    ) {
    }

    #[RpcMethod(
        'hr.memoryPages.editComment',
        'создание страницы памяти',
        examples: [
            [
                'summary' => 'Добавить комментарий к странице памяти',
                'params'  => [
                    'commentId' => 1,
                    'text'      => 'текст комментария',
                    'photos'    => [
                        [
                            'id'       => 1,
                            'toDelete' => false,
                        ],
                        [
                            'base64' => 'новая фотка',
                        ],
                        [
                            'id'     => 1,
                            'base64' => 'отредактировать старую фотку',
                        ],
                    ],
                ],
            ],
        ],
        isAutomapped: true
    )]
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
    public function __invoke(EditCommentRequest $request): array
    {
        $comment = $this->useCase->editComment($request);

        return $comment->toArray();
    }
}
