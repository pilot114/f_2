<?php

declare(strict_types=1);

namespace App\Domain\Hr\Achievements\Controller;

use App\Common\Attribute\RpcMethod;
use App\Common\DTO\FindResponse;
use App\Domain\Hr\Achievements\Entity\Color;
use App\Domain\Hr\Achievements\UseCase\GetCategoriesColorsUseCase;

class GetCategoriesColorsController
{
    public function __construct(
        private GetCategoriesColorsUseCase $useCase
    ) {
    }

    /**
     * @return FindResponse<Color>
     */
    #[RpcMethod(
        'hr.achievements.getCategoriesColors',
        'Получить цвета категорий достижений',
    )]
    public function __invoke(): FindResponse
    {
        $data = $this->useCase->getColors();
        return new FindResponse($data);
    }
}
