<?php

declare(strict_types=1);

namespace App\Domain\Hr\MemoryPages\Controller;

use App\Common\Attribute\RpcMethod;
use App\Domain\Hr\MemoryPages\DTO\AddCommentRequest;
use App\Domain\Hr\MemoryPages\UseCase\AddCommentUseCase;

class AddCommentController
{
    public function __construct(
        private AddCommentUseCase $useCase
    ) {
    }

    #[RpcMethod(
        'hr.memoryPages.addComment',
        'создание страницы памяти',
        examples: [
            [
                'summary' => 'Добавить комментарий к странице памяти',
                'params'  => [
                    'memoryPageId' => 1,
                    'text'         => 'текст комментария',
                    'photos'       => [
                        [
                            'base64' => 'новая фотка в base64',
                        ],
                        [
                            'base64' => 'еще одна фотка base64',
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
    public function __invoke(AddCommentRequest $request): array
    {
        $comment = $this->useCase->addComment($request);

        return $comment->toArray();
    }
}
