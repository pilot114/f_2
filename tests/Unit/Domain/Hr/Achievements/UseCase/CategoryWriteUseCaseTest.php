<?php

declare(strict_types=1);

namespace App\Tests\Unit\Hr\Achievements\UseCase;

use App\Domain\Hr\Achievements\Entity\Category;
use App\Domain\Hr\Achievements\Entity\Color;
use App\Domain\Hr\Achievements\UseCase\CategoryWriteUseCase;
use Database\ORM\Attribute\Loader;
use Database\ORM\CommandRepositoryInterface;
use Database\ORM\QueryRepositoryInterface;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

beforeEach(function (): void {
    $this->commandRepository = $this->createMock(CommandRepositoryInterface::class);
    $this->readRepository = $this->createMock(QueryRepositoryInterface::class);
    $this->colorRepository = $this->createMock(QueryRepositoryInterface::class);

    $this->useCase = new CategoryWriteUseCase(
        $this->commandRepository,
        $this->readRepository,
        $this->colorRepository
    );
});

it('creates new category successfully', function (): void {
    $color = new Color(1, 'https://example.com/color.png', 123);

    $this->colorRepository
        ->expects($this->once())
        ->method('findOrFail')
        ->with(1, 'Не найден цвет')
        ->willReturn($color);

    $this->readRepository
        ->expects($this->once())
        ->method('findOneBy')
        ->with([
            'name' => 'New Category',
        ])
        ->willReturn(null); // Category doesn't exist

    $expectedCategory = new Category(
        id: 1, // Set by repository
        name: 'New Category',
        isPersonal: 0,
        isHidden: 0,
        color: $color
    );

    $this->commandRepository
        ->expects($this->once())
        ->method('create')
        ->with($this->callback(function (Category $category): bool {
            return $category->id === Loader::ID_FOR_INSERT;
        }))
        ->willReturn($expectedCategory);

    $result = $this->useCase->create('New Category', 1, false, false);

    expect($result)->toBe($expectedCategory);
});

it('throws conflict exception when category name already exists', function (): void {
    $color = new Color(1, 'https://example.com/color.png', 123);
    $existingCategory = new Category(1, 'Existing Category', 0, 0, $color);

    $this->colorRepository
        ->expects($this->once())
        ->method('findOrFail')
        ->with(1, 'Не найден цвет')
        ->willReturn($color);

    $this->readRepository
        ->expects($this->once())
        ->method('findOneBy')
        ->with([
            'name' => 'Existing Category',
        ])
        ->willReturn($existingCategory);

    $this->commandRepository
        ->expects($this->never())
        ->method('create');

    expect(fn () => $this->useCase->create('Existing Category', 1, false, false))
        ->toThrow(ConflictHttpException::class, 'Категория с названием Existing Category уже существует');
});

it('updates existing category successfully', function (): void {
    $oldColor = new Color(1, 'https://example.com/old-color.png', 123);
    $newColor = new Color(2, 'https://example.com/new-color.png', 456);
    $category = new Category(1, 'Old Name', 0, 0, $oldColor);

    $this->colorRepository
        ->expects($this->once())
        ->method('findOrFail')
        ->with(2, 'Не найден цвет')
        ->willReturn($newColor);

    $this->readRepository
        ->expects($this->once())
        ->method('find')
        ->with(1)
        ->willReturn($category);

    $this->commandRepository
        ->expects($this->once())
        ->method('update')
        ->with($category)
        ->willReturn($category);

    $result = $this->useCase->update(1, 'New Name', 2, true, true);

    expect($result)->toBe($category);
});

it('throws not found exception when updating non-existent category', function (): void {
    $color = new Color(1, 'https://example.com/color.png', 123);

    $this->colorRepository
        ->expects($this->once())
        ->method('findOrFail')
        ->with(1, 'Не найден цвет')
        ->willReturn($color);

    $this->readRepository
        ->expects($this->once())
        ->method('find')
        ->with(999)
        ->willReturn(null);

    $this->commandRepository
        ->expects($this->never())
        ->method('update');

    expect(fn () => $this->useCase->update(999, 'New Name', 1, false, false))
        ->toThrow(NotFoundHttpException::class, 'Не существует категории с id = 999');
});

it('throws exception when color not found during creation', function (): void {
    $this->colorRepository
        ->expects($this->once())
        ->method('findOrFail')
        ->with(999, 'Не найден цвет')
        ->willThrowException(new NotFoundHttpException('Не найден цвет'));

    $this->readRepository
        ->expects($this->never())
        ->method('findOneBy');

    expect(fn () => $this->useCase->create('New Category', 999, false, false))
        ->toThrow(NotFoundHttpException::class, 'Не найден цвет');
});

it('throws exception when color not found during update', function (): void {
    $this->colorRepository
        ->expects($this->once())
        ->method('findOrFail')
        ->with(999, 'Не найден цвет')
        ->willThrowException(new NotFoundHttpException('Не найден цвет'));

    $this->readRepository
        ->expects($this->never())
        ->method('find');

    expect(fn () => $this->useCase->update(1, 'New Name', 999, false, false))
        ->toThrow(NotFoundHttpException::class, 'Не найден цвет');
});

it('creates personal category', function (): void {
    $color = new Color(1, 'https://example.com/color.png', 123);

    $this->colorRepository
        ->expects($this->once())
        ->method('findOrFail')
        ->with(1, 'Не найден цвет')
        ->willReturn($color);

    $this->readRepository
        ->expects($this->once())
        ->method('findOneBy')
        ->with([
            'name' => 'Personal Category',
        ])
        ->willReturn(null);

    $expectedCategory = new Category(
        id: 1,
        name: 'Personal Category',
        isPersonal: 1,
        isHidden: 0,
        color: $color
    );

    $this->commandRepository
        ->expects($this->once())
        ->method('create')
        ->willReturn($expectedCategory);

    $result = $this->useCase->create('Personal Category', 1, true, false);

    expect($result)->toBe($expectedCategory);
});
