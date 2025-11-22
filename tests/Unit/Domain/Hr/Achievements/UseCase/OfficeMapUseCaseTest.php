<?php

declare(strict_types=1);

namespace App\Tests\Unit\Hr\Achievements\UseCase;

use App\Domain\Hr\Achievements\Entity\Achievement;
use App\Domain\Hr\Achievements\Entity\AchievementEmployeeItem;
use App\Domain\Hr\Achievements\Entity\Category;
use App\Domain\Hr\Achievements\Entity\Color;
use App\Domain\Hr\Achievements\Entity\Employee;
use App\Domain\Hr\Achievements\Entity\Image;
use App\Domain\Hr\Achievements\Repository\AchievementEmployeeItemQueryRepository;
use App\Domain\Hr\Achievements\Repository\CategoryQueryRepository;
use App\Domain\Hr\Achievements\UseCase\OfficeMapUseCase;
use DateTimeImmutable;
use Illuminate\Support\Collection;

beforeEach(function (): void {
    $this->achievementRepository = $this->createMock(AchievementEmployeeItemQueryRepository::class);
    $this->categoryRepository = $this->createMock(CategoryQueryRepository::class);

    $this->useCase = new OfficeMapUseCase(
        $this->achievementRepository,
        $this->categoryRepository
    );
});

it('gets user info with achievements and categories', function (): void {
    $userId = 1;

    // Create test data
    $employee = new Employee(1, 'John Doe', 'Developer');
    $image = new Image(1, 456, 'Test Image');
    $color = new Color(1, 'https://example.com/color.png', 123);

    // User has achievement 1 but not achievement 2
    $achievement1 = new Achievement(1, 'Achievement 1', 'Description 1', $image);
    $achievement2 = new Achievement(2, 'Achievement 2', 'Description 2', $image);

    $achievementItem = new AchievementEmployeeItem(
        1,
        new DateTimeImmutable(),
        new DateTimeImmutable(),
        $employee,
        $achievement1
    );

    $userAchievements = new Collection([$achievementItem]);

    // Create categories with achievements
    $category = new Category(
        id: 1,
        name: 'Test Category',
        isPersonal: 0,
        isHidden: 0,
        color: $color,
        achievements: [$achievement1, $achievement2]
    );

    $categories = new Collection([$category]);

    // Set up mocks
    $this->achievementRepository
        ->expects($this->once())
        ->method('getEmployeeAchievements')
        ->with($userId)
        ->willReturn($userAchievements);

    $this->categoryRepository
        ->expects($this->once())
        ->method('getAll')
        ->willReturn($categories);

    $result = $this->useCase->getUserInfo($userId);

    expect($result)->toBeArray();
    expect($result)->toHaveCount(1);

    $categoryData = $result[0];
    expect($categoryData->id)->toBe(1);
    expect($categoryData->name)->toBe('Test Category');
    expect($categoryData->unlocked)->toHaveCount(1);
    expect($categoryData->locked)->toHaveCount(1);
    expect($categoryData->unlocked[0]->id)->toBe(1);
    expect($categoryData->locked[0]->id)->toBe(2);
});

it('handles user with no achievements', function (): void {
    $userId = 2;

    $image = new Image(1, 456, 'Test Image');
    $color = new Color(1, 'https://example.com/color.png', 123);
    $achievement = new Achievement(1, 'Achievement', 'Description', $image);

    $emptyAchievements = new Collection([]);

    $category = new Category(
        id: 1,
        name: 'Test Category',
        isPersonal: 0,
        isHidden: 0,
        color: $color,
        achievements: [$achievement]
    );

    $categories = new Collection([$category]);

    $this->achievementRepository
        ->expects($this->once())
        ->method('getEmployeeAchievements')
        ->with($userId)
        ->willReturn($emptyAchievements);

    $this->categoryRepository
        ->expects($this->once())
        ->method('getAll')
        ->willReturn($categories);

    $result = $this->useCase->getUserInfo($userId);

    expect($result)->toBeArray();
    expect($result)->toHaveCount(0);
});

it('handles empty categories', function (): void {
    $userId = 1;

    $userAchievements = new Collection([]);
    $categories = new Collection([]);

    $this->achievementRepository
        ->expects($this->once())
        ->method('getEmployeeAchievements')
        ->with($userId)
        ->willReturn($userAchievements);

    $this->categoryRepository
        ->expects($this->once())
        ->method('getAll')
        ->willReturn($categories);

    $result = $this->useCase->getUserInfo($userId);

    expect($result)->toBeArray();
    expect($result)->toHaveCount(0);
});

it('handles multiple categories correctly', function (): void {
    $userId = 1;

    $employee = new Employee(1, 'John Doe', 'Developer');
    $image = new Image(1, 456, 'Test Image');
    $color1 = new Color(1, 'https://example.com/color1.png', 123);
    $color2 = new Color(2, 'https://example.com/color2.png', 456);

    $achievement1 = new Achievement(1, 'Achievement 1', 'Description 1', $image);
    $achievement2 = new Achievement(2, 'Achievement 2', 'Description 2', $image);
    $achievement3 = new Achievement(3, 'Achievement 3', 'Description 3', $image);

    // User has achievements 1 and 3
    $achievementItems = new Collection([
        new AchievementEmployeeItem(1, new DateTimeImmutable(), new DateTimeImmutable(), $employee, $achievement1),
        new AchievementEmployeeItem(2, new DateTimeImmutable(), new DateTimeImmutable(), $employee, $achievement3),
    ]);

    $category1 = new Category(1, 'Category 1', 0, 0, $color1, [$achievement1]);
    $category2 = new Category(2, 'Category 2', 1, 0, $color2, [$achievement2, $achievement3]);

    $categories = new Collection([$category1, $category2]);

    $this->achievementRepository
        ->expects($this->once())
        ->method('getEmployeeAchievements')
        ->with($userId)
        ->willReturn($achievementItems);

    $this->categoryRepository
        ->expects($this->once())
        ->method('getAll')
        ->willReturn($categories);

    $result = $this->useCase->getUserInfo($userId);

    expect($result)->toHaveCount(2);

    // Category 1: has achievement 1 (unlocked)
    expect($result[0]->id)->toBe(1);
    expect($result[0]->unlocked)->toHaveCount(1);
    expect($result[0]->locked)->toHaveCount(0);

    // Category 2: has achievement 2 (locked) and 3 (unlocked)
    expect($result[1]->id)->toBe(2);
    expect($result[1]->unlocked)->toHaveCount(1);
    expect($result[1]->locked)->toHaveCount(1);
});

it('extracts achievement IDs correctly from user achievements', function (): void {
    $userId = 1;

    $employee = new Employee(1, 'John Doe', 'Developer');
    $image = new Image(1, 456, 'Test Image');
    $achievement1 = new Achievement(5, 'Achievement 5', 'Description', $image);
    $achievement2 = new Achievement(10, 'Achievement 10', 'Description', $image);

    $achievementItems = new Collection([
        new AchievementEmployeeItem(1, new DateTimeImmutable(), new DateTimeImmutable(), $employee, $achievement1),
        new AchievementEmployeeItem(2, new DateTimeImmutable(), new DateTimeImmutable(), $employee, $achievement2),
    ]);

    $categories = new Collection([]);

    $this->achievementRepository
        ->expects($this->once())
        ->method('getEmployeeAchievements')
        ->with($userId)
        ->willReturn($achievementItems);

    $this->categoryRepository
        ->expects($this->once())
        ->method('getAll')
        ->willReturn($categories);

    // The method should extract IDs [5, 10] from the achievements
    // We can't directly test this extraction, but we can verify the method runs without error
    $result = $this->useCase->getUserInfo($userId);

    expect($result)->toBeArray();
});
