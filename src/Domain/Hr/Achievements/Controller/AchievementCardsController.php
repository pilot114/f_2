<?php

declare(strict_types=1);

namespace App\Domain\Hr\Achievements\Controller;

use App\Common\Attribute\RpcMethod;
use App\Common\Attribute\RpcParam;
use App\Common\DTO\FindResponse;
use App\Domain\Hr\Achievements\DTO\AchievementResponse;
use App\Domain\Hr\Achievements\DTO\AchievementSlimResponse;
use App\Domain\Hr\Achievements\Entity\Achievement;
use App\Domain\Hr\Achievements\UseCase\AchievementCardsReadUseCase;
use App\Domain\Hr\Achievements\UseCase\AchievementCardsWriteUseCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AchievementCardsController
{
    public function __construct(
        private AchievementCardsReadUseCase  $readUseCase,
        private AchievementCardsWriteUseCase $writeUseCase,
    ) {
    }

    /**
     * @return FindResponse<AchievementSlimResponse>
     */
    #[RpcMethod(
        'hr.achievements.getAchievementCards',
        'Получить карточки достижений',
    )]
    public function getAll(): FindResponse
    {
        $entities = $this->readUseCase->getAchievementCards()
            ->map(fn (Achievement $item): AchievementSlimResponse => $item->toAchievementSlimResponse())
        ;
        return new FindResponse($entities);
    }

    #[RpcMethod(
        'hr.achievements.getAchievementCardById',
        'Получить карточку достижений по id',
        examples: [
            [
                'summary' => 'Получить карточку №44',
                'params'  => [
                    'id' => 94,
                ],
            ],
        ],
    )]
    public function getById(#[RpcParam('Id карточки')] int $id): AchievementResponse
    {
        $achievement = $this->readUseCase->getAchievementCardById($id);
        if (!$achievement instanceof Achievement) {
            throw new NotFoundHttpException("Карточка достижения с id = $id не найдена");
        }
        return $achievement->toAchievementResponse(withoutAchievements: true);
    }

    #[RpcMethod(
        'hr.achievements.deleteAchievementCardById',
        'Удалить карточку достижений по id',
        examples: [
            [
                'summary' => 'Удалить карточку №12',
                'params'  => [
                    'id' => 12,
                ],
            ],
        ],
    )]
    public function delete(#[RpcParam('Id карточки')] int $id): bool
    {
        return $this->writeUseCase->deleteAchievement($id);
    }

    #[RpcMethod(
        'hr.achievements.createAchievementCard',
        'Создать карточку достижений',
        examples: [
            [
                'summary' => 'Создать карточку в 1 категории с названием test и изображением №3',
                'params'  => [
                    'categoriesId'   => 1,
                    'name'           => 'test',
                    'imageLibraryId' => 3,
                    'description'    => 'Тестовая категория',
                ],
            ],
        ],
    )]
    public function create(
        #[RpcParam('Id категории')] int         $categoriesId,
        #[RpcParam('Название карточки')] string $name,
        #[RpcParam('Id изображения')] int       $imageLibraryId,
        #[RpcParam('Описание карточки')] string $description
    ): AchievementResponse {
        return $this->writeUseCase
            ->create($categoriesId, $name, $imageLibraryId, $description)
            ->toAchievementResponse(withoutAchievements: true);
    }

    #[RpcMethod(
        'hr.achievements.updateAchievementCard',
        'Перезаписать карточку достижений по id',
        examples: [
            [
                'summary' => 'Перезаписать карточку №42',
                'params'  => [
                    'id'             => 42,
                    'categoriesId'   => 1,
                    'name'           => 'Новое название',
                    'imageLibraryId' => 3,
                    'description'    => 'Обновили карточку',
                ],
            ],
        ],
    )]
    public function update(
        #[RpcParam('Id карточки')] int          $id,
        #[RpcParam('Id категории')] int         $categoriesId,
        #[RpcParam('Название карточки')] string $name,
        #[RpcParam('Id изображения')] int       $imageLibraryId,
        #[RpcParam('Описание карточки')] string $description
    ): ?AchievementResponse {
        return $this->writeUseCase
            ->update($id, $categoriesId, $name, $imageLibraryId, $description)
            ->toAchievementResponse(withoutAchievements: true);
    }

    #[RpcMethod(
        'hr.achievements.checkCardNameExist',
        'Проверить, доступно ли название карточки достижений',
        examples: [
            [
                'summary' => 'Есть ли карточка Трудяжка?',
                'params'  => [
                    'name' => 'Трудяжка',
                ],
            ],
        ],
    )]
    public function checkName(#[RpcParam('Название категории')] string $name): bool
    {
        return $this->readUseCase->cardIsExist($name);
    }
}
