<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Hr\Achievements\DTO;

use App\Domain\Hr\Achievements\DTO\ColorResponse;

it('creates color response with all fields', function (): void {
    $response = new ColorResponse(
        id: 1,
        url: '/colors/red.png',
        fileId: 100,
    );

    expect($response->id)->toBe(1)
        ->and($response->url)->toBe('/colors/red.png')
        ->and($response->fileId)->toBe(100);
});

it('handles different file ids', function (): void {
    $response = new ColorResponse(id: 1, url: '/test.png', fileId: 999);

    expect($response->fileId)->toBe(999);
});

it('handles absolute urls', function (): void {
    $response = new ColorResponse(
        id: 1,
        url: 'https://example.com/color.png',
        fileId: 1,
    );

    expect($response->url)->toBe('https://example.com/color.png');
});

it('handles relative urls', function (): void {
    $response = new ColorResponse(
        id: 1,
        url: '/api/v2/file/100/view',
        fileId: 100,
    );

    expect($response->url)->toBe('/api/v2/file/100/view');
});

it('handles url with query parameters', function (): void {
    $response = new ColorResponse(
        id: 1,
        url: '/colors/red.png?version=2',
        fileId: 1,
    );

    expect($response->url)->toBe('/colors/red.png?version=2');
});

it('handles zero file id', function (): void {
    $response = new ColorResponse(id: 1, url: '/test.png', fileId: 0);

    expect($response->fileId)->toBe(0);
});
