<?php

declare(strict_types=1);

namespace App\Domain\Hr\Achievements\Entity;

use App\Domain\Hr\Achievements\DTO\AchievementResponse;
use App\Domain\Hr\Achievements\DTO\CategoryOfficeMapResponse;
use App\Domain\Hr\Achievements\DTO\CategoryResponse;
use App\Domain\Hr\Achievements\DTO\CategoryWithoutAchievementsResponse;
use Database\ORM\Attribute\Column;
use Database\ORM\Attribute\Entity;

#[Entity(name: 'TEST.CP_EA_CATEGORIES', sequenceName: 'TEST.CP_EA_CATEGORIES_SQ')]
class Category
{
    public function __construct(
        #[Column(name: 'id')] public int              $id,
        #[Column(name: 'name')] protected string      $name,
        #[Column(name: 'is_personal')] protected int  $isPersonal,
        #[Column(name: 'is_hidden')] protected int    $isHidden,
        #[Column(name: 'colors_id')] protected ?Color $color = null,
        #[Column(name: 'achievements', collectionOf: Achievement::class)]
        protected array $achievements = [],
    ) {
    }

    public function update(string $name, Color $color, bool $isPersonal, bool $isHidden): Category
    {
        $this->name = $name;
        $this->color = $color;
        $this->isPersonal = (int) $isPersonal;
        $this->isHidden = (int) $isHidden;

        return $this;
    }

    public function toCategoryWithoutAchievementsResponse(): CategoryWithoutAchievementsResponse
    {
        return new CategoryWithoutAchievementsResponse(
            id: $this->id,
            name: $this->name,
            isPersonal: (bool) $this->isPersonal,
            isHidden: (bool) $this->isHidden,
            color: $this->color?->toColorResponse(),
        );
    }

    public function toCategoryResponse(): CategoryResponse
    {
        $achievements = array_map(static fn (Achievement $x): AchievementResponse => $x->toAchievementResponse(), $this->achievements);

        return new CategoryResponse(
            id: $this->id,
            name: $this->name,
            isPersonal: (bool) $this->isPersonal,
            isHidden: (bool) $this->isHidden,
            color: $this->color?->toColorResponse(),
            achievements: array_values($achievements)
        );
    }

    public function toCategoryOfficeMapResponse(array $userAchievementsIds, array $receivedMap = []): CategoryOfficeMapResponse
    {
        $unlocked = [];
        $locked = [];

        /** @var Achievement $achievement */
        foreach ($this->achievements as $achievement) {
            if (in_array($achievement->id, $userAchievementsIds, true)) {
                $unlocked[] = $achievement->toAchievementForOfficeMapResponse($receivedMap[$achievement->id] ?? []);
            } else {
                // если категория скрытая, то не показываем заблокированные ачивки
                if ($this->isHidden !== 0) {
                    continue;
                }
                $locked[] = $achievement->toAchievementForOfficeMapResponse($receivedMap[$achievement->id] ?? []);
            }
        }

        return new CategoryOfficeMapResponse(
            id: $this->id,
            name: $this->name,
            isPersonal: (bool) $this->isPersonal,
            color: $this->color?->toColorResponse(),
            cardCount: count($this->achievements),
            locked: $locked,
            unlocked: $unlocked,
        );
    }
}
