<?php

declare(strict_types=1);

namespace App\Domain\Marketing\AdventCalendar\Controller;

use App\Common\Attribute\RpcMethod;
use App\Common\DTO\FindResponse;
use App\Domain\Marketing\AdventCalendar\Entity\BackgroundImage;
use App\Domain\Marketing\AdventCalendar\UseCase\GetBackgroundImagesUseCase;

readonly class GetBackgroundImagesController
{
    public function __construct(
        private GetBackgroundImagesUseCase $getBackgroundImagesUseCase,
    ) {
    }

    /**
     * @return FindResponse<BackgroundImage>
     */
    #[RpcMethod(
        'marketing.adventCalendar.getBackgroundImages',
        'Получение списка фоновых картинок для заполнения предложения',
        examples: [
            [
                'summary' => 'Список фоновых картинок',
                'params'  => [],
            ],
        ],
    )]
    public function getBackgroundImages(): FindResponse
    {
        return new FindResponse($this->getBackgroundImagesUseCase->getData());
    }
}
