<?php

declare(strict_types=1);

namespace App\Tests\Unit\Hr\Achievements\UseCase;

use App\Domain\Hr\Achievements\Entity\Category;
use App\Domain\Hr\Achievements\Entity\Color;
use App\Domain\Hr\Achievements\Repository\CategoryQueryRepository;
use App\Domain\Hr\Achievements\UseCase\CategoriesReadUseCase;
use Illuminate\Support\Collection;

beforeEach(function (): void {
    $this->repository = $this->createMock(CategoryQueryRepository::class);
    $this->useCase = new CategoriesReadUseCase($this->repository);
});

it('gets all categories', function (): void {
    $color = new Color(1, 'https://example.com/color.png', 123);
    $category1 = new Category(1, 'Category 1', 0, 0, $color);
    $category2 = new Category(2, 'Category 2', 1, 0, $color);
    $categories = new Collection([$category1, $category2]);

    $this->repository
        ->expects($this->once())
        ->method('getAll')
        ->willReturn($categories);

    $result = $this->useCase->getAll();

    expect($result)->toBe($categories);
    expect($result)->toHaveCount(2);
});

it('returns empty collection when no categories', function (): void {
    $emptyCollection = new Collection([]);

    $this->repository
        ->expects($this->once())
        ->method('getAll')
        ->willReturn($emptyCollection);

    $result = $this->useCase->getAll();

    expect($result)->toBe($emptyCollection);
    expect($result)->toHaveCount(0);
});

it('gets category by id', function (): void {
    $color = new Color(1, 'https://example.com/color.png', 123);
    $category = new Category(1, 'Test Category', 0, 0, $color);

    $this->repository
        ->expects($this->once())
        ->method('getById')
        ->with(1)
        ->willReturn($category);

    $result = $this->useCase->getById(1);

    expect($result)->toBe($category);
});

it('returns null when category not found by id', function (): void {
    $this->repository
        ->expects($this->once())
        ->method('getById')
        ->with(999)
        ->willReturn(null);

    $result = $this->useCase->getById(999);

    expect($result)->toBeNull();
});

it('checks if category exists by name', function (): void {
    $color = new Color(1, 'https://example.com/color.png', 123);
    $category = new Category(1, 'Existing Category', 0, 0, $color);

    $this->repository
        ->expects($this->once())
        ->method('findOneBy')
        ->with([
            'name' => 'Existing Category',
        ])
        ->willReturn($category);

    $result = $this->useCase->categoryIsExist('Existing Category');

    expect($result)->toBeTrue();
});

it('returns false when category does not exist by name', function (): void {
    $this->repository
        ->expects($this->once())
        ->method('findOneBy')
        ->with([
            'name' => 'Non Existent Category',
        ])
        ->willReturn(null);

    $result = $this->useCase->categoryIsExist('Non Existent Category');

    expect($result)->toBeFalse();
});

it('checks category existence with empty name', function (): void {
    $this->repository
        ->expects($this->once())
        ->method('findOneBy')
        ->with([
            'name' => '',
        ])
        ->willReturn(null);

    $result = $this->useCase->categoryIsExist('');

    expect($result)->toBeFalse();
});

it('checks category existence with special characters in name', function (): void {
    $color = new Color(1, 'https://example.com/color.png', 123);
    $category = new Category(1, 'Категория со спецсимволами & знаками', 0, 0, $color);

    $this->repository
        ->expects($this->once())
        ->method('findOneBy')
        ->with([
            'name' => 'Категория со спецсимволами & знаками',
        ])
        ->willReturn($category);

    $result = $this->useCase->categoryIsExist('Категория со спецсимволами & знаками');

    expect($result)->toBeTrue();
});
