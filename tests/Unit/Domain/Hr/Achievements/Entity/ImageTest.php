<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Hr\Achievements\Entity;

use App\Domain\Hr\Achievements\Entity\Image;

it('creates image with all fields', function (): void {
    $image = new Image(
        id: 1,
        fileId: 100,
        name: 'Trophy Icon',
    );

    expect($image->id)->toBe(1);
});

it('converts to image response', function (): void {
    $image = new Image(
        id: 5,
        fileId: 200,
        name: 'Star Badge',
    );

    $response = $image->toImageResponse();

    expect($response->id)->toBe(5)
        ->and($response->name)->toBe('Star Badge')
        ->and($response->url)->toBe('/api/v2/file/200/view');
});

it('generates correct url with file id', function (): void {
    $image = new Image(
        id: 10,
        fileId: 42,
        name: 'Achievement',
    );

    $response = $image->toImageResponse();

    expect($response->url)->toBe('/api/v2/file/42/view');
});

it('handles different image names', function (string $name): void {
    $image = new Image(
        id: 1,
        fileId: 1,
        name: $name,
    );

    $response = $image->toImageResponse();

    expect($response->name)->toBe($name);
})->with([
    'Gold Medal',
    'Silver Trophy',
    'Bronze Award',
    'Значок отличия',
    'Achievement #1',
]);

it('handles cyrillic names', function (): void {
    $image = new Image(
        id: 1,
        fileId: 100,
        name: 'Награда за успех',
    );

    $response = $image->toImageResponse();

    expect($response->name)->toBe('Награда за успех');
});

it('handles large file ids', function (): void {
    $image = new Image(
        id: 1,
        fileId: 999999,
        name: 'Test',
    );

    $response = $image->toImageResponse();

    expect($response->url)->toBe('/api/v2/file/999999/view');
});

it('is readonly', function (): void {
    $image = new Image(
        id: 1,
        fileId: 1,
        name: 'Test',
    );

    expect($image)->toBeInstanceOf(Image::class);
});
