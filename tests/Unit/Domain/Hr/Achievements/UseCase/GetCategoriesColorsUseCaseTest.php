<?php

declare(strict_types=1);

namespace App\Tests\Unit\Hr\Achievements\UseCase;

use App\Common\Service\File\FileService;
use App\Domain\Hr\Achievements\Entity\Color;
use App\Domain\Hr\Achievements\UseCase\GetCategoriesColorsUseCase;
use App\Domain\Portal\Files\Entity\File;
use Database\ORM\QueryRepositoryInterface;
use Illuminate\Support\Collection;

beforeEach(function (): void {
    $this->repository = $this->createMock(QueryRepositoryInterface::class);
    $this->fileService = $this->createMock(FileService::class);
    $this->useCase = new GetCategoriesColorsUseCase($this->repository, $this->fileService);
});

it('gets colors with file URLs', function (): void {
    $color1 = new Color(1, 'old-url1.png', 123);
    $color2 = new Color(2, 'old-url2.png', 456);
    $colors = new Collection([$color1, $color2]);

    $file1 = $this->createMock(File::class);
    $file1->method('getId')->willReturn(123);

    $file2 = $this->createMock(File::class);
    $file2->method('getId')->willReturn(456);

    $this->repository
        ->expects($this->once())
        ->method('findAll')
        ->willReturn($colors);

    $this->fileService
        ->expects($this->exactly(2))
        ->method('getById')
        ->willReturnMap([
            [123, $file1],
            [456, $file2],
        ]);

    $this->fileService
        ->expects($this->exactly(2))
        ->method('getStaticUrl')
        ->willReturnMap([
            [$file1, 'https://example.com/file/123.png'],
            [$file2, 'https://example.com/file/456.png'],
        ]);

    $result = $this->useCase->getColors();

    expect($result)->toBe($colors);
    expect($result)->toHaveCount(2);
});

it('skips colors when file not found', function (): void {
    $color1 = new Color(1, 'old-url1.png', 123);
    $color2 = new Color(2, 'old-url2.png', 456);
    $colors = new Collection([$color1, $color2]);

    $file1 = $this->createMock(File::class);
    $file1->method('getId')->willReturn(123);

    $this->repository
        ->expects($this->once())
        ->method('findAll')
        ->willReturn($colors);

    $this->fileService
        ->expects($this->exactly(2))
        ->method('getById')
        ->willReturnMap([
            [123, $file1],
            [456, null], // File not found
        ]);

    $this->fileService
        ->expects($this->once())
        ->method('getStaticUrl')
        ->with($file1)
        ->willReturn('https://example.com/file/123.png');

    $result = $this->useCase->getColors();

    expect($result)->toBe($colors);
    expect($result)->toHaveCount(2);

    // First color should be updated, second should remain unchanged
    expect($color1->toColorResponse()->url)->toBe('https://example.com/file/123.png');
});

it('returns empty collection when no colors', function (): void {
    $emptyCollection = new Collection([]);

    $this->repository
        ->expects($this->once())
        ->method('findAll')
        ->willReturn($emptyCollection);

    $this->fileService
        ->expects($this->never())
        ->method('getById');

    $result = $this->useCase->getColors();

    expect($result)->toBe($emptyCollection);
    expect($result)->toHaveCount(0);
});

it('handles file service returning non-File objects', function (): void {
    $color = new Color(1, 'old-url.png', 123);
    $colors = new Collection([$color]);

    $this->repository
        ->expects($this->once())
        ->method('findAll')
        ->willReturn($colors);

    $this->fileService
        ->expects($this->once())
        ->method('getById')
        ->with(123)
        ->willReturn(null); // Method returns null when file not found

    $this->fileService
        ->expects($this->never())
        ->method('getStaticUrl');

    $result = $this->useCase->getColors();

    expect($result)->toBe($colors);
    // Color should remain unchanged when file not found
    expect($color->toColorResponse()->url)->toBe('old-url.png');
});
