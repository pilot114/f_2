<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Hr\Achievements\Entity;

use App\Domain\Hr\Achievements\Entity\Achievement;
use App\Domain\Hr\Achievements\Entity\Category;
use App\Domain\Hr\Achievements\Entity\Color;
use App\Domain\Hr\Achievements\Entity\Image;

it('creates achievement with all fields', function (): void {
    $image = new Image(id: 1, fileId: 100, name: 'Trophy');
    $color = new Color(id: 1, url: '/colors/red.png', fileId: 10);
    $category = new Category(id: 1, name: 'Excellence', isPersonal: 1, isHidden: 0, color: $color);

    $achievement = new Achievement(
        id: 1,
        name: 'Best Employee',
        description: 'Top performer',
        image: $image,
        category: $category,
    );

    expect($achievement->id)->toBe(1);
});

it('creates achievement without category', function (): void {
    $image = new Image(id: 1, fileId: 1, name: 'Icon');

    $achievement = new Achievement(
        id: 1,
        name: 'Test',
        description: 'Test description',
        image: $image,
    );

    expect($achievement->id)->toBe(1);
});

it('updates achievement fields', function (): void {
    $image1 = new Image(id: 1, fileId: 1, name: 'Old');
    $image2 = new Image(id: 2, fileId: 2, name: 'New');
    $color = new Color(id: 1, url: '/test.png', fileId: 1);
    $category1 = new Category(id: 1, name: 'Old', isPersonal: 0, isHidden: 0, color: $color);
    $category2 = new Category(id: 2, name: 'New', isPersonal: 1, isHidden: 0, color: $color);

    $achievement = new Achievement(
        id: 1,
        name: 'Old Name',
        description: 'Old Description',
        image: $image1,
        category: $category1,
    );

    $result = $achievement->update('New Name', 'New Description', $category2, $image2);

    expect($result)->toBe($achievement);
});

it('converts to achievement slim response', function (): void {
    $image = new Image(id: 1, fileId: 1, name: 'Icon');
    $achievement = new Achievement(
        id: 5,
        name: 'Excellence Award',
        description: 'Test',
        image: $image,
    );

    $response = $achievement->toAchievementSlimResponse();

    expect($response->id)->toBe(5)
        ->and($response->name)->toBe('Excellence Award');
});

it('converts to achievement for office map response', function (): void {
    $image = new Image(id: 1, fileId: 100, name: 'Trophy');
    $achievement = new Achievement(
        id: 1,
        name: 'Best',
        description: 'Top performer',
        image: $image,
        userCount: 42,
    );

    $response = $achievement->toAchievementForOfficeMapResponse(['user1', 'user2']);

    expect($response->id)->toBe(1)
        ->and($response->name)->toBe('Best')
        ->and($response->description)->toBe('Top performer')
        ->and($response->userCount)->toBe(42)
        ->and($response->received)->toBe(['user1', 'user2']);
});

it('converts to achievement for office map with empty received', function (): void {
    $image = new Image(id: 1, fileId: 1, name: 'Icon');
    $achievement = new Achievement(
        id: 1,
        name: 'Test',
        description: 'Test',
        image: $image,
    );

    $response = $achievement->toAchievementForOfficeMapResponse([]);

    expect($response->received)->toBeEmpty();
});

it('converts to achievement response with category', function (): void {
    $image = new Image(id: 1, fileId: 100, name: 'Trophy');
    $color = new Color(id: 1, url: '/colors/red.png', fileId: 10);
    $category = new Category(id: 1, name: 'Excellence', isPersonal: 1, isHidden: 0, color: $color);

    $achievement = new Achievement(
        id: 5,
        name: 'Award',
        description: 'Description',
        image: $image,
        category: $category,
        userCount: 10,
    );

    $response = $achievement->toAchievementResponse();

    expect($response->id)->toBe(5)
        ->and($response->name)->toBe('Award')
        ->and($response->description)->toBe('Description')
        ->and($response->userCount)->toBe(10)
        ->and($response->category)->not->toBeNull();
});

it('converts to achievement response without achievements in category', function (): void {
    $image = new Image(id: 1, fileId: 1, name: 'Icon');
    $color = new Color(id: 1, url: '/test.png', fileId: 1);
    $category = new Category(id: 1, name: 'Test', isPersonal: 0, isHidden: 0, color: $color);

    $achievement = new Achievement(
        id: 1,
        name: 'Test',
        description: 'Test',
        image: $image,
        category: $category,
    );

    $response = $achievement->toAchievementResponse(withoutAchievements: true);

    expect($response->category)->not->toBeNull();
});

it('converts to achievement response without category', function (): void {
    $image = new Image(id: 1, fileId: 1, name: 'Icon');

    $achievement = new Achievement(
        id: 1,
        name: 'Test',
        description: 'Test',
        image: $image,
    );

    $response = $achievement->toAchievementResponse();

    expect($response->category)->toBeNull();
});

it('handles cyrillic in achievement name and description', function (): void {
    $image = new Image(id: 1, fileId: 1, name: 'Иконка');

    $achievement = new Achievement(
        id: 1,
        name: 'Лучший сотрудник',
        description: 'Выдающиеся достижения',
        image: $image,
    );

    $response = $achievement->toAchievementResponse();

    expect($response->name)->toBe('Лучший сотрудник')
        ->and($response->description)->toBe('Выдающиеся достижения');
});
