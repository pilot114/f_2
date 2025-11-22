<?php

declare(strict_types=1);

namespace App\Tests\Unit\Hr\Achievements\UseCase;

use App\Domain\Hr\Achievements\Entity\Achievement;
use App\Domain\Hr\Achievements\Entity\Category;
use App\Domain\Hr\Achievements\Entity\Color;
use App\Domain\Hr\Achievements\Entity\Image;
use App\Domain\Hr\Achievements\Repository\AchievementQueryRepository;
use App\Domain\Hr\Achievements\Repository\CategoryQueryRepository;
use App\Domain\Hr\Achievements\UseCase\AchievementCardsWriteUseCase;
use Database\ORM\Attribute\Loader;
use Database\ORM\CommandRepositoryInterface;
use Database\ORM\QueryRepositoryInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

beforeEach(function (): void {
    $this->readRepository = $this->createMock(AchievementQueryRepository::class);
    $this->commandRepository = $this->createMock(CommandRepositoryInterface::class);
    $this->categoryRepository = $this->createMock(CategoryQueryRepository::class);
    $this->imageRepository = $this->createMock(QueryRepositoryInterface::class);

    $this->useCase = new AchievementCardsWriteUseCase(
        $this->readRepository,
        $this->commandRepository,
        $this->categoryRepository,
        $this->imageRepository
    );
});

it('deletes achievement successfully', function (): void {
    $this->commandRepository
        ->expects($this->once())
        ->method('delete')
        ->with(1)
        ->willReturn(true);

    $result = $this->useCase->deleteAchievement(1);

    expect($result)->toBeTrue();
});

it('creates new achievement', function (): void {
    $color = new Color(1, 'https://example.com/color.png', 123);
    $category = new Category(1, 'Test Category', 0, 0, $color);
    $image = new Image(1, 456, 'Test Image');

    $this->categoryRepository
        ->expects($this->once())
        ->method('getById')
        ->with(1)
        ->willReturn($category);

    $this->readRepository
        ->expects($this->once())
        ->method('nameExist')
        ->with('Test Achievement')
        ->willReturn(false);

    $this->imageRepository
        ->expects($this->once())
        ->method('findOrFail')
        ->with(1, 'Не найдено изображение')
        ->willReturn($image);

    $expectedAchievement = new Achievement(
        id: 1, // This would be set by the repository
        name: 'Test Achievement',
        description: 'Test Description',
        category: $category,
        image: $image
    );

    $this->commandRepository
        ->expects($this->once())
        ->method('create')
        ->with($this->callback(function (Achievement $achievement): bool {
            return $achievement->id === Loader::ID_FOR_INSERT;
        }))
        ->willReturn($expectedAchievement);

    $result = $this->useCase->create(1, 'Test Achievement', 1, 'Test Description');

    expect($result)->toBe($expectedAchievement);
});

it('updates existing achievement', function (): void {
    $color = new Color(1, 'https://example.com/color.png', 123);
    $category = new Category(1, 'Test Category', 0, 0, $color);
    $image = new Image(1, 456, 'Test Image');

    $existingAchievement = new Achievement(1, 'Old Name', 'Old Description', $image);

    $this->readRepository
        ->expects($this->once())
        ->method('getById')
        ->with(1)
        ->willReturn($existingAchievement);

    $this->categoryRepository
        ->expects($this->once())
        ->method('findOrFail')
        ->with(1, 'Не найдена категория')
        ->willReturn($category);

    $this->imageRepository
        ->expects($this->once())
        ->method('findOrFail')
        ->with(1, 'Не найдено изображение')
        ->willReturn($image);

    $this->commandRepository
        ->expects($this->once())
        ->method('update')
        ->with($existingAchievement)
        ->willReturn($existingAchievement);

    $result = $this->useCase->update(1, 1, 'New Name', 1, 'New Description');

    expect($result)->toBe($existingAchievement);
});

it('throws exception when updating non-existent achievement', function (): void {
    $this->readRepository
        ->expects($this->once())
        ->method('getById')
        ->with(999)
        ->willReturn(null);

    expect(fn () => $this->useCase->update(999, 1, 'New Name', 1, 'New Description'))
        ->toThrow(NotFoundHttpException::class, 'Не найдено достижение с id = 999');
});
