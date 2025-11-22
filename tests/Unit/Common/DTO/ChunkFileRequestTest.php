<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Common\Files\Dto;

use App\Domain\Portal\Files\Dto\ChunkFileRequest;

it('creates chunk file request with all required fields', function (): void {
    $request = new ChunkFileRequest(
        uploadId: 'upload-123',
        chunkIndex: 0,
        chunkCount: 5,
        fileName: 'test.pdf',
        collection: 'documents',
    );

    expect($request->uploadId)->toBe('upload-123')
        ->and($request->chunkIndex)->toBe(0)
        ->and($request->chunkCount)->toBe(5)
        ->and($request->fileName)->toBe('test.pdf')
        ->and($request->collection)->toBe('documents')
        ->and($request->idInCollection)->toBeNull();
});

it('creates chunk file request with optional idInCollection', function (): void {
    $request = new ChunkFileRequest(
        uploadId: 'upload-456',
        chunkIndex: 2,
        chunkCount: 10,
        fileName: 'document.docx',
        collection: 'reports',
        idInCollection: 42,
    );

    expect($request->uploadId)->toBe('upload-456')
        ->and($request->chunkIndex)->toBe(2)
        ->and($request->chunkCount)->toBe(10)
        ->and($request->fileName)->toBe('document.docx')
        ->and($request->collection)->toBe('reports')
        ->and($request->idInCollection)->toBe(42);
});

it('handles first chunk', function (): void {
    $request = new ChunkFileRequest(
        uploadId: 'upload-789',
        chunkIndex: 0,
        chunkCount: 1,
        fileName: 'single-chunk.txt',
        collection: 'text',
    );

    expect($request->chunkIndex)->toBe(0)
        ->and($request->chunkCount)->toBe(1);
});

it('handles last chunk in multi-chunk upload', function (): void {
    $request = new ChunkFileRequest(
        uploadId: 'upload-999',
        chunkIndex: 4,
        chunkCount: 5,
        fileName: 'large-file.zip',
        collection: 'archives',
    );

    expect($request->chunkIndex)->toBe(4)
        ->and($request->chunkCount)->toBe(5);
});

it('handles file names with special characters', function (): void {
    $request = new ChunkFileRequest(
        uploadId: 'upload-special',
        chunkIndex: 0,
        chunkCount: 1,
        fileName: 'Файл с пробелами & символами.pdf',
        collection: 'documents',
    );

    expect($request->fileName)->toBe('Файл с пробелами & символами.pdf');
});
