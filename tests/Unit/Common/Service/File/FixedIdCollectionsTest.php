<?php

declare(strict_types=1);

namespace App\Tests\Unit\Common\Service\File;

use App\Common\Service\File\FixedIdCollections;
use App\Domain\Hr\MemoryPages\MemoryPagePhotoService;
use App\Domain\Portal\Files\Entity\File;
use InvalidArgumentException;

it('throws exception when idInCollection is null for fixed id collection', function (string $collection): void {
    // Act & Assert
    expect(fn () => FixedIdCollections::check($collection, null))
        ->toThrow(InvalidArgumentException::class, 'idInCollection обязателен и не может быть сгенерирован автоматически в коллекции ' . $collection);
})->with([
    File::USERPIC_COLLECTION,
    MemoryPagePhotoService::OTHER_IMAGE_COLLECTION,
    MemoryPagePhotoService::COMMENTS_IMAGE_COLLECTION,
    MemoryPagePhotoService::MAIN_IMAGE_COLLECTION,
]);

it('does not throw exception when idInCollection is not null for fixed id collection', function (string $collection): void {
    // Act & Assert
    FixedIdCollections::check($collection, 123);
    expect(true)->toBeTrue(); // No exception thrown
})->with([
    File::USERPIC_COLLECTION,
    MemoryPagePhotoService::OTHER_IMAGE_COLLECTION,
    MemoryPagePhotoService::COMMENTS_IMAGE_COLLECTION,
    MemoryPagePhotoService::MAIN_IMAGE_COLLECTION,
]);

it('does not throw exception for non-fixed id collection', function (): void {
    // Act & Assert
    FixedIdCollections::check('some_other_collection', null);
    expect(true)->toBeTrue(); // No exception thrown
});
