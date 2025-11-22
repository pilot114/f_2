<?php

declare(strict_types=1);

namespace App\Domain\Hr\MemoryPages\Controller;

use App\Common\Attribute\CpAction;
use App\Common\Attribute\RpcMethod;
use App\Domain\Hr\MemoryPages\DTO\CreateMemoryPageRequest;
use App\Domain\Hr\MemoryPages\UseCase\CreateMemoryPageUseCase;

class CreateMemoryPageController
{
    public function __construct(
        private CreateMemoryPageUseCase $useCase
    ) {
    }

    #[RpcMethod(
        'hr.memoryPages.createMemoryPage',
        'создание страницы памяти',
        examples: [
            [
                'summary' => 'создание страницы памяти',
                'params'  => [
                    'employeeId'      => 1,
                    'birthDate'       => '2000-08-15T15:52:01+00:00',
                    'deathDate'       => '2025-08-15T15:52:01+00:00',
                    'obituary'        => 'test',
                    'obituaryFull'    => '<p>test</p>',
                    'mainPhotoBase64' => 'test',
                    'workPeriods'     => [
                        [
                            'startDate'    => '2020-08-15T15:52:01+00:00',
                            'endDate'      => '2025-08-15T15:52:01+00:00',
                            'responseId'   => 1,
                            'achievements' => 'test',
                        ],
                    ],
                    'otherPhotos' => [
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
    #[CpAction('memory_pages.memory_pages_add')]
    /**
     * @return array{
     *     id: int,
     *     employee: array{
     *         id: int,
     *         name: string,
     *         response: array{
     *             id: int,
     *             name: string,
     *         },
     *         avatar: array{
     *             original: string,
     *             small: string,
     *             medium: string,
     *             large: string,
     *         },
     *     },
     *     birthDate: string,
     *     deathDate: string,
     *     createDate: string,
     *     obituary: string,
     *     obituaryFull: string,
     *     mainPhoto: array{
     *         id: int,
     *         urls: array<array{
     *             original: string,
     *             small: string,
     *             medium: string,
     *             large: string,
     *         }>
     *     },
     *     otherPhotos: array<array{
     *         original: string,
     *         small: string,
     *         medium: string,
     *         large: string,
     *     }>,
     *     response: array{
     *         id: int,
     *         name: string,
     *     },
     *     workPeriods: array<array{
     *         id: int,
     *         memoryPageId: int,
     *         startDate: string,
     *         endDate: string,
     *         response: array{
     *             id: int,
     *             name: string,
     *         },
     *         achievements?: string,
     *     }>,
     *     comments: array<array{
     *         id: int,
     *         employee: array{
     *             id: int,
     *             name: string,
     *             response?: array{
     *                 id: int,
     *                 name: string,
     *             },
     *             avatar: array{
     *                 original: string,
     *                 small: string,
     *                 medium: string,
     *                 large: string,
     *             },
     *         },
     *         text: string,
     *         isPinned: bool,
     *         createDate: string,
     *         photos: array<array{
     *             original: string,
     *             small: string,
     *             medium: string,
     *             large: string,
     *         }>,
     *     }>
     * }
     */
    public function __invoke(CreateMemoryPageRequest $request): array
    {
        $memoryPage = $this->useCase->create($request);

        return $memoryPage->toArray();
    }
}
