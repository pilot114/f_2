<?php

declare(strict_types=1);

namespace App\Domain\Hr\Achievements\Controller;

use App\Common\Attribute\RpcMethod;
use App\Common\Attribute\RpcParam;
use App\Domain\Hr\Achievements\DTO\CategoryResponse;
use App\Domain\Hr\Achievements\UseCase\CategoryWriteUseCase;

class CategoriesWriteController
{
    public function __construct(
        private CategoryWriteUseCase $useCase
    ) {
    }

    #[RpcMethod(
        'hr.achievements.createCategory',
        'Создать новую категорию',
        examples: [
            [
                'summary' => 'Создание тестовой категории',
                'params'  => [
                    'name'       => 'Test',
                    'colorId'    => 3,
                    'isPersonal' => 0,
                ],
            ],
        ],
    )]
    public function create(
        #[RpcParam('Название категории')] string $name,
        #[RpcParam('Цвет категории')] int        $colorId,
        #[RpcParam('Является ли личной')] bool   $isPersonal,
        #[RpcParam('Не отображать как доступное к получению')] bool $isHidden,
    ): CategoryResponse {
        return $this->useCase->create($name, $colorId, $isPersonal, $isHidden)->toCategoryResponse();
    }

    #[RpcMethod(
        'hr.achievements.updateCategory',
        'Изменить категорию',
        examples: [
            [
                'summary' => 'Изменить категорию #12',
                'params'  => [
                    'id'         => 12,
                    'name'       => 'Тест переименования',
                    'colorId'    => 3,
                    'isPersonal' => false,
                ],
            ],
        ],
    )]
    public function update(
        #[RpcParam('Id категории')] int    $id,
        #[RpcParam('Название категории')] string $name,
        #[RpcParam('Цвет категории')] int        $colorId,
        #[RpcParam('Является ли личной')] bool   $isPersonal,
        #[RpcParam('Не отображать как доступное к получению')] bool $isHidden,
    ): CategoryResponse {
        return $this->useCase->update($id, $name, $colorId, $isPersonal, $isHidden)->toCategoryResponse();
    }
}
