<?php

declare(strict_types=1);

namespace App\Tests\Unit\Hr\Achievements\UseCase;

use App\Domain\Hr\Achievements\Entity\Image;
use App\Domain\Hr\Achievements\UseCase\GetImagesUseCase;
use Database\ORM\QueryRepositoryInterface;
use Illuminate\Support\Collection;

beforeEach(function (): void {
    $this->repository = $this->createMock(QueryRepositoryInterface::class);
    $this->useCase = new GetImagesUseCase($this->repository);
});

it('gets all images', function (): void {
    $image1 = new Image(1, 123, 'Image 1');
    $image2 = new Image(2, 456, 'Image 2');
    $images = new Collection([$image1, $image2]);

    $this->repository
        ->expects($this->once())
        ->method('findAll')
        ->willReturn($images);

    $result = $this->useCase->getImages();

    expect($result)->toBe($images);
    expect($result)->toHaveCount(2);
});

it('returns empty collection when no images', function (): void {
    $emptyCollection = new Collection([]);

    $this->repository
        ->expects($this->once())
        ->method('findAll')
        ->willReturn($emptyCollection);

    $result = $this->useCase->getImages();

    expect($result)->toBe($emptyCollection);
    expect($result)->toHaveCount(0);
});
