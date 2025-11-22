<?php

declare(strict_types=1);

use App\Domain\Portal\Files\Entity\File;

beforeEach(function (): void {
    $this->file = new File(
        id: 123,
        name: 'test-document',
        filePath: '/uploads/test-document.pdf',
        userId: 42,
        idInCollection: 10,
        collectionName: 'documents',
        extension: 'pdf',
        lastEditedDate: new DateTimeImmutable('2024-01-15 10:30:00'),
        isOnStatic: 1
    );
});

it('gets id', function (): void {
    expect($this->file->getId())->toBe(123);
});

it('gets user id', function (): void {
    expect($this->file->getUserId())->toBe(42);
});

it('gets collection name', function (): void {
    expect($this->file->getCollectionName())->toBe('documents');
});

it('gets id in collection', function (): void {
    expect($this->file->getIdInCollection())->toBe(10);
});

it('gets file path', function (): void {
    expect($this->file->getFilePath())->toBe('/uploads/test-document.pdf');
});

it('gets last edited date', function (): void {
    $date = $this->file->getLastEditedDate();
    expect($date)->toBeInstanceOf(DateTimeImmutable::class)
        ->and($date->format('Y-m-d H:i:s'))->toBe('2024-01-15 10:30:00');
});

it('checks if userpic collection', function (): void {
    $userpicFile = new File(
        id: 1,
        name: 'avatar',
        filePath: '/userpics/avatar.jpg',
        userId: 1,
        idInCollection: 1,
        collectionName: 'userpic',
        extension: 'jpg'
    );

    expect($userpicFile->isUserpic())->toBeTrue()
        ->and($this->file->isUserpic())->toBeFalse();
});

it('checks if image by extension', function (): void {
    $jpgFile = new File(
        id: 1, name: 'photo', filePath: '/photo.jpg',
        userId: 1, idInCollection: 1, collectionName: 'photos',
        extension: 'jpg'
    );
    $pngFile = new File(
        id: 2, name: 'image', filePath: '/image.png',
        userId: 1, idInCollection: 1, collectionName: 'photos',
        extension: 'PNG'
    );
    $pdfFile = new File(
        id: 3, name: 'doc', filePath: '/doc.pdf',
        userId: 1, idInCollection: 1, collectionName: 'docs',
        extension: 'pdf'
    );

    expect($jpgFile->isImage())->toBeTrue()
        ->and($pngFile->isImage())->toBeTrue()
        ->and($pdfFile->isImage())->toBeFalse();
});

it('gets name for download with extension', function (): void {
    $file = new File(
        id: 1, name: 'document', filePath: '/doc.pdf',
        userId: 1, idInCollection: 1, collectionName: 'docs',
        extension: 'pdf'
    );

    expect($file->getNameForDownload())->toBe('document.pdf');
});

it('gets name for download when name already has extension', function (): void {
    $file = new File(
        id: 1, name: 'document.pdf', filePath: '/doc.pdf',
        userId: 1, idInCollection: 1, collectionName: 'docs',
        extension: 'pdf'
    );

    expect($file->getNameForDownload())->toBe('document.pdf');
});

it('gets name for download case insensitive', function (): void {
    $file = new File(
        id: 1, name: 'document.PDF', filePath: '/doc.pdf',
        userId: 1, idInCollection: 1, collectionName: 'docs',
        extension: 'PDF'
    );

    expect($file->getNameForDownload())->toBe('document.PDF');
});

it('updates file data', function (): void {
    $originalDate = $this->file->getLastEditedDate();
    sleep(1);

    $this->file->updateFileData('/new/path/file.txt', 'new-name', 'txt');

    expect($this->file->getFilePath())->toBe('/new/path/file.txt')
        ->and($this->file->getLastEditedDate())->not->toBe($originalDate);
});

it('gets image urls with different sizes', function (): void {
    $imageFile = new File(
        id: 999,
        name: 'photo',
        filePath: '/photo.jpg',
        userId: 1,
        idInCollection: 1,
        collectionName: 'photos',
        extension: 'jpg',
        lastEditedDate: new DateTimeImmutable('2024-01-20 15:00:00')
    );

    $urls = $imageFile->getImageUrls();

    expect($urls)->toBeArray()
        ->and($urls)->toHaveKeys(['original', 'small', 'medium', 'large'])
        ->and($urls['original'])->toContain('/999/')
        ->and($urls['small'])->toContain('/999/')
        ->and($urls['medium'])->toContain('/999/')
        ->and($urls['large'])->toContain('/999/');
});

it('converts to file response', function (): void {
    $response = $this->file->toFileResponse();

    expect($response)->toBeObject()
        ->and($response->id)->toBe(123)
        ->and($response->name)->toBe('test-document')
        ->and($response->extension)->toBe('pdf')
        ->and($response->collectionName)->toBe('documents')
        ->and($response->idInCollection)->toBe(10)
        ->and($response->downloadUrl)->toBeString()
        ->and($response->viewUrl)->toBeString();
});

it('converts to array', function (): void {
    $array = $this->file->toArray();

    expect($array)->toBeArray()
        ->and($array['id'])->toBe(123)
        ->and($array['name'])->toBe('test-document')
        ->and($array['extension'])->toBe('pdf')
        ->and($array['userId'])->toBe(42)
        ->and($array['lastEditedDate'])->toBeString()
        ->and($array['downloadUrl'])->toBeString()
        ->and($array['viewUrl'])->toBeString()
        ->and($array['collection'])->toBeArray()
        ->and($array['collection']['idInCollection'])->toBe(10)
        ->and($array['collection']['name'])->toBe('documents');
});

it('formats last edited date in ISO format', function (): void {
    $array = $this->file->toArray();

    expect($array['lastEditedDate'])->toMatch('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}[+-]\d{2}:\d{2}$/');
});
