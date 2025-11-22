<?php

declare(strict_types=1);

namespace App\Domain\Hr\Achievements\Controller;

use App\Common\Attribute\RpcMethod;
use App\Common\Attribute\RpcParam;
use App\Common\DTO\FindResponse;
use App\Domain\Hr\Achievements\DTO\CategoryOfficeMapResponse;
use App\Domain\Hr\Achievements\UseCase\OfficeMapUseCase;

class GetForOfficeMapController
{
    public function __construct(
        private OfficeMapUseCase $useCase,
    ) {
    }

    /**
     * @return FindResponse<CategoryOfficeMapResponse>
     */
    #[RpcMethod(
        'hr.achievements.getForOfficeMap',
        'Получить карточки достижений',
        examples: [
            [
                'summary' => 'Получить информацию о достижениях Артура Дента',
                'params'  => [
                    'userId' => 42,
                ],
            ],
        ],
    )]
    public function get(#[RpcParam('Id пользователя')] int $userId): FindResponse
    {
        $data = $this->useCase->getUserInfo($userId);
        return new FindResponse($data);
    }
}
