<?php

declare(strict_types=1);

namespace App\Domain\Hr\Achievements\Controller;

use App\Common\Attribute\RpcMethod;
use App\Common\DTO\FindResponse;
use App\Domain\Hr\Achievements\DTO\ImageResponse;
use App\Domain\Hr\Achievements\Entity\Image;
use App\Domain\Hr\Achievements\UseCase\GetImagesUseCase;

class GetImagesController
{
    public function __construct(
        private GetImagesUseCase $useCase
    ) {
    }

    /**
     * @return FindResponse<ImageResponse>
     */
    #[RpcMethod(
        'hr.achievements.getImages',
        'Получить список изображений',
    )]
    public function __invoke(): FindResponse
    {
        $data = $this->useCase->getImages()
            ->map(fn (Image $image): ImageResponse => $image->toImageResponse())
        ;
        return new FindResponse($data);
    }
}
