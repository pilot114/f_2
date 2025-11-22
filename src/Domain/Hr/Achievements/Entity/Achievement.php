<?php

declare(strict_types=1);

namespace App\Domain\Hr\Achievements\Entity;

use App\Domain\Hr\Achievements\DTO\AchievementForOfficeMapResponse;
use App\Domain\Hr\Achievements\DTO\AchievementResponse;
use App\Domain\Hr\Achievements\DTO\AchievementSlimResponse;
use Database\ORM\Attribute\{Column, Entity};

#[Entity(name: 'TEST.CP_EA_ACHIEVEMENT_CARDS', sequenceName: 'TEST.CP_EA_ACHIEVEMENT_CARDS_SQ')]
class Achievement
{
    public function __construct(
        #[Column(name: 'id')] public int                $id,
        #[Column(name: 'name')] private string          $name,
        #[Column(name: 'description')] private string   $description,

        #[Column(name: 'image_library_id')] private Image  $image,
        #[Column(name: 'categories_id')] private ?Category $category = null,

        #[Column(name: 'user_count', onlyForRead: true)]
        private int $userCount = 0,
    ) {
    }

    public function update(string $name, string $description, Category $category, Image $image): Achievement
    {
        $this->name = $name;
        $this->description = $description;

        $this->category = $category;
        $this->image = $image;

        return $this;
    }

    public function toAchievementSlimResponse(): AchievementSlimResponse
    {
        return new AchievementSlimResponse(
            id: $this->id,
            name: $this->name
        );
    }

    public function toAchievementForOfficeMapResponse(array $received): AchievementForOfficeMapResponse
    {
        return new AchievementForOfficeMapResponse(
            id: $this->id,
            name: $this->name,
            description: $this->description,
            image: $this->image->toImageResponse(),
            userCount: $this->userCount,
            received: $received,
        );
    }

    public function toAchievementResponse(bool $withoutAchievements = false): AchievementResponse
    {
        if ($withoutAchievements) {
            $category = $this->category?->toCategoryWithoutAchievementsResponse();
        } else {
            $category = $this->category?->toCategoryResponse();
        }

        return new AchievementResponse(
            id: $this->id,
            name: $this->name,
            description: $this->description,
            category: $category,
            image: $this->image->toImageResponse(),
            userCount: $this->userCount,
        );
    }
}
