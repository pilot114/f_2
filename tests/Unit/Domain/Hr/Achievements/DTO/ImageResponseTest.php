<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Hr\Achievements\DTO;

use App\Domain\Hr\Achievements\DTO\ImageResponse;

it('creates image response with all fields', function (): void {
    $response = new ImageResponse(
        id: 1,
        name: 'Trophy',
        url: '/api/v2/file/100/view',
    );

    expect($response->id)->toBe(1)
        ->and($response->name)->toBe('Trophy')
        ->and($response->url)->toBe('/api/v2/file/100/view');
});

it('handles different urls', function (): void {
    $response = new ImageResponse(
        id: 1,
        name: 'Medal',
        url: 'https://cdn.example.com/medal.png',
    );

    expect($response->url)->toBe('https://cdn.example.com/medal.png');
});

it('handles cyrillic names', function (): void {
    $response = new ImageResponse(
        id: 1,
        name: 'Кубок победителя',
        url: '/files/trophy.png',
    );

    expect($response->name)->toBe('Кубок победителя');
});

it('handles empty name', function (): void {
    $response = new ImageResponse(id: 1, name: '', url: '/test.png');

    expect($response->name)->toBe('');
});

it('handles special characters in name', function (): void {
    $response = new ImageResponse(
        id: 1,
        name: 'Trophy & Medal #1',
        url: '/test.png',
    );

    expect($response->name)->toBe('Trophy & Medal #1');
});

it('handles long names', function (): void {
    $name = 'This is a very long image name that describes what this image represents';
    $response = new ImageResponse(id: 1, name: $name, url: '/test.png');

    expect($response->name)->toBe($name);
});
