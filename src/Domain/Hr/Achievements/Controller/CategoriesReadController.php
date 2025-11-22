<?php

declare(strict_types=1);

namespace App\Domain\Hr\Achievements\Controller;

use App\Common\Attribute\{RpcMethod, RpcParam};
use App\Common\DTO\FindResponse;
use App\Domain\Hr\Achievements\DTO\CategoryResponse;
use App\Domain\Hr\Achievements\DTO\CategoryWithoutAchievementsResponse;
use App\Domain\Hr\Achievements\Entity\Category;
use App\Domain\Hr\Achievements\UseCase\CategoriesReadUseCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CategoriesReadController
{
    public function __construct(
        private CategoriesReadUseCase $useCase,
    ) {
    }

    /**
     * @return FindResponse<CategoryWithoutAchievementsResponse>
     */
    #[RpcMethod(
        'hr.achievements.getCategories',
        'Получить данные по категориям достижений',
    )]
    public function getCategories(): FindResponse
    {
        $items = $this->useCase->getAll()
            ->map(fn (Category $category): CategoryWithoutAchievementsResponse => $category->toCategoryWithoutAchievementsResponse())
        ;
        return new FindResponse($items);
    }

    /**
     * @return FindResponse<CategoryResponse>
     */
    #[RpcMethod(
        'hr.achievements.getCategoriesWithAchievements',
        'Получить данные по категориям достижений, включая сами достижения',
    )]
    public function getCategoriesWithAchievements(): FindResponse
    {
        $items = $this->useCase->getAll()
            ->map(fn (Category $category): CategoryResponse => $category->toCategoryResponse())
        ;
        return new FindResponse($items);
    }

    #[RpcMethod(
        'hr.achievements.getCategoryById',
        'Получить категорию по id',
        examples: [
            [
                'summary' => 'Получить категорию №31',
                'params'  => [
                    'id' => 31,
                ],
            ],
        ],
    )]
    public function getCategory(#[RpcParam('id категории')] int $id): CategoryResponse
    {
        $category = $this->useCase->getById($id);
        if (!$category instanceof Category) {
            throw new NotFoundHttpException("Категория с id = $id не найдена");
        }
        return $category->toCategoryResponse();
    }

    #[RpcMethod(
        'hr.achievements.checkCategoryNameExist',
        'Проверить, доступно ли название категории достижений',
        examples: [
            [
                'summary' => 'Есть ли категория Образование?',
                'params'  => [
                    'name' => 'Образование',
                ],
            ],
        ],
    )]
    public function checkName(#[RpcParam('Название категории')] string $name): bool
    {
        return $this->useCase->categoryIsExist($name);
    }
}
