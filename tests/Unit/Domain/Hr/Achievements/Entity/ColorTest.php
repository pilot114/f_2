<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Hr\Achievements\Entity;

use App\Domain\Hr\Achievements\Entity\Color;

it('creates color with all fields', function (): void {
    $color = new Color(
        id: 1,
        url: '/images/color-red.png',
        fileId: 100,
    );

    expect($color->id)->toBe(1)
        ->and($color->getFileId())->toBe(100);
});

it('returns correct file id', function (): void {
    $color = new Color(
        id: 1,
        url: '/images/color-blue.png',
        fileId: 42,
    );

    expect($color->getFileId())->toBe(42);
});

it('sets file data', function (): void {
    $color = new Color(
        id: 1,
        url: '/old-url.png',
        fileId: 10,
    );

    $result = $color->setFile(fileId: 50, url: '/new-url.png');

    expect($result)->toBe($color)
        ->and($color->getFileId())->toBe(50);
});

it('converts to color response', function (): void {
    $color = new Color(
        id: 5,
        url: '/images/green.png',
        fileId: 200,
    );

    $response = $color->toColorResponse();

    expect($response->id)->toBe(5)
        ->and($response->url)->toBe('/images/green.png')
        ->and($response->fileId)->toBe(200);
});

it('setFile returns self for chaining', function (): void {
    $color = new Color(
        id: 1,
        url: '/original.png',
        fileId: 1,
    );

    $result = $color->setFile(fileId: 2, url: '/updated.png')
        ->setFile(fileId: 3, url: '/final.png');

    expect($result)->toBe($color)
        ->and($color->getFileId())->toBe(3);
});

it('handles different url formats', function (string $url): void {
    $color = new Color(
        id: 1,
        url: $url,
        fileId: 1,
    );

    $response = $color->toColorResponse();

    expect($response->url)->toBe($url);
})->with([
    '/images/red.png',
    '/static/colors/blue.jpg',
    'https://example.com/color.webp',
    '/api/v2/file/123/view',
]);

it('handles file id updates', function (): void {
    $color = new Color(
        id: 1,
        url: '/test.png',
        fileId: 100,
    );

    $color->setFile(fileId: 999, url: '/test.png');

    expect($color->getFileId())->toBe(999);
});
