<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Common\Files\Dto;

use App\Domain\Portal\Files\Dto\FileResponse;

it('creates file response with all fields', function (): void {
    $response = new FileResponse(
        id: 1,
        name: 'document',
        extension: 'pdf',
        downloadUrl: 'https://example.com/download/1',
        viewUrl: 'https://example.com/view/1',
        collectionName: 'documents',
        idInCollection: 42,
    );

    expect($response->id)->toBe(1)
        ->and($response->name)->toBe('document')
        ->and($response->extension)->toBe('pdf')
        ->and($response->downloadUrl)->toBe('https://example.com/download/1')
        ->and($response->viewUrl)->toBe('https://example.com/view/1')
        ->and($response->collectionName)->toBe('documents')
        ->and($response->idInCollection)->toBe(42);
});

it('handles different file extensions', function (string $extension): void {
    $response = new FileResponse(
        id: 1,
        name: 'file',
        extension: $extension,
        downloadUrl: 'https://example.com/download/1',
        viewUrl: 'https://example.com/view/1',
        collectionName: 'files',
        idInCollection: 1,
    );

    expect($response->extension)->toBe($extension);
})->with(['pdf', 'docx', 'xlsx', 'jpg', 'png', 'txt', 'zip']);

it('handles file names with cyrillic characters', function (): void {
    $response = new FileResponse(
        id: 10,
        name: 'Документ с русскими буквами',
        extension: 'pdf',
        downloadUrl: 'https://example.com/download/10',
        viewUrl: 'https://example.com/view/10',
        collectionName: 'documents',
        idInCollection: 100,
    );

    expect($response->name)->toBe('Документ с русскими буквами');
});

it('handles different collection names', function (): void {
    $response = new FileResponse(
        id: 5,
        name: 'image',
        extension: 'jpg',
        downloadUrl: 'https://example.com/download/5',
        viewUrl: 'https://example.com/view/5',
        collectionName: 'user_avatars',
        idInCollection: 999,
    );

    expect($response->collectionName)->toBe('user_avatars')
        ->and($response->idInCollection)->toBe(999);
});
