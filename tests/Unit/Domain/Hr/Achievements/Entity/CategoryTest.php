<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Hr\Achievements\Entity;

use App\Domain\Hr\Achievements\Entity\Achievement;
use App\Domain\Hr\Achievements\Entity\Category;
use App\Domain\Hr\Achievements\Entity\Color;
use App\Domain\Hr\Achievements\Entity\Image;

it('creates category with all fields', function (): void {
    $color = new Color(id: 1, url: '/colors/red.png', fileId: 10);
    $category = new Category(
        id: 1,
        name: 'Excellence',
        isPersonal: 1,
        isHidden: 0,
        color: $color,
    );

    expect($category->id)->toBe(1);
});

it('creates category without color', function (): void {
    $category = new Category(
        id: 1,
        name: 'Team Achievement',
        isPersonal: 0,
        isHidden: 0,
    );

    expect($category->id)->toBe(1);
});

it('updates category fields', function (): void {
    $oldColor = new Color(id: 1, url: '/old.png', fileId: 1);
    $newColor = new Color(id: 2, url: '/new.png', fileId: 2);

    $category = new Category(
        id: 1,
        name: 'Old Name',
        isPersonal: 0,
        isHidden: 0,
        color: $oldColor,
    );

    $result = $category->update('New Name', $newColor, true, false);

    expect($result)->toBe($category);
});

it('converts to category without achievements response', function (): void {
    $color = new Color(id: 1, url: '/colors/blue.png', fileId: 10);
    $category = new Category(
        id: 5,
        name: 'Leadership',
        isPersonal: 1,
        isHidden: 0,
        color: $color,
    );

    $response = $category->toCategoryWithoutAchievementsResponse();

    expect($response->id)->toBe(5)
        ->and($response->name)->toBe('Leadership')
        ->and($response->isPersonal)->toBeTrue();
});

it('converts to category without achievements response when no color', function (): void {
    $category = new Category(
        id: 1,
        name: 'Test',
        isPersonal: 0,
        isHidden: 0,
    );

    $response = $category->toCategoryWithoutAchievementsResponse();

    expect($response->id)->toBe(1)
        ->and($response->color)->toBeNull();
});

it('converts to category response with achievements', function (): void {
    $color = new Color(id: 1, url: '/colors/red.png', fileId: 10);
    $image = new Image(id: 1, fileId: 100, name: 'Trophy');
    $achievement = new Achievement(
        id: 1,
        name: 'Best Employee',
        description: 'Top performer',
        image: $image,
    );

    $category = new Category(
        id: 1,
        name: 'Excellence',
        isPersonal: 1,
        isHidden: 0,
        color: $color,
        achievements: [$achievement],
    );

    $response = $category->toCategoryResponse();

    expect($response->id)->toBe(1)
        ->and($response->name)->toBe('Excellence')
        ->and($response->isPersonal)->toBeTrue()
        ->and($response->achievements)->toHaveCount(1);
});

it('converts to category response with empty achievements', function (): void {
    $color = new Color(id: 1, url: '/test.png', fileId: 1);
    $category = new Category(
        id: 1,
        name: 'Test',
        isPersonal: 0,
        isHidden: 0,
        color: $color,
    );

    $response = $category->toCategoryResponse();

    expect($response->achievements)->toBeEmpty();
});

it('converts to category office map response', function (): void {
    $color = new Color(id: 1, url: '/colors/red.png', fileId: 10);
    $image = new Image(id: 1, fileId: 100, name: 'Trophy');
    $achievement1 = new Achievement(id: 1, name: 'First', description: 'Test', image: $image);
    $achievement2 = new Achievement(id: 2, name: 'Second', description: 'Test', image: $image);

    $category = new Category(
        id: 1,
        name: 'Test',
        isPersonal: 1,
        isHidden: 0,
        color: $color,
        achievements: [$achievement1, $achievement2],
    );

    $response = $category->toCategoryOfficeMapResponse([1]);

    expect($response->cardCount)->toBe(2)
        ->and(count($response->unlocked))->toBe(1)
        ->and(count($response->locked))->toBe(1);
});

it('handles office map with no unlocked achievements', function (): void {
    $image = new Image(id: 1, fileId: 1, name: 'Icon');
    $achievement = new Achievement(id: 1, name: 'Test', description: 'Test', image: $image);

    $category = new Category(
        id: 1,
        name: 'Test',
        isPersonal: 0,
        isHidden: 0,
        achievements: [$achievement],
    );

    $response = $category->toCategoryOfficeMapResponse([]);

    expect(count($response->unlocked))->toBe(0)
        ->and(count($response->locked))->toBe(1);
});

it('handles office map with all unlocked achievements', function (): void {
    $image = new Image(id: 1, fileId: 1, name: 'Icon');
    $achievement = new Achievement(id: 1, name: 'Test', description: 'Test', image: $image);

    $category = new Category(
        id: 1,
        name: 'Test',
        isPersonal: 0,
        isHidden: 0,
        achievements: [$achievement],
    );

    $response = $category->toCategoryOfficeMapResponse([1]);

    expect(count($response->unlocked))->toBe(1)
        ->and(count($response->locked))->toBe(0);
});

it('handles cyrillic names in category', function (): void {
    $category = new Category(
        id: 1,
        name: 'Лидерство',
        isPersonal: 1,
        isHidden: 0,
    );

    $response = $category->toCategoryWithoutAchievementsResponse();

    expect($response->name)->toBe('Лидерство');
});
