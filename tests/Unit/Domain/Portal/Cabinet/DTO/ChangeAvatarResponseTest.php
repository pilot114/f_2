<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\User\Cabinet\DTO;

use App\Domain\Portal\Cabinet\DTO\ChangeAvatarResponse;
use App\Domain\Portal\Files\Entity\File;
use Mockery;

it('builds from file entity', function (): void {
    $file = Mockery::mock(File::class);
    $file->shouldReceive('getUserId')->andReturn(42);
    $file->shouldReceive('getImageUrls')->andReturn([
        'original' => '/files/original.jpg',
        'small'    => '/files/small.jpg',
        'medium'   => '/files/medium.jpg',
        'large'    => '/files/large.jpg',
    ]);

    $response = ChangeAvatarResponse::build($file);

    expect($response->userId)->toBe(42)
        ->and($response->original)->toBe('/files/original.jpg')
        ->and($response->small)->toBe('/files/small.jpg')
        ->and($response->medium)->toBe('/files/medium.jpg')
        ->and($response->large)->toBe('/files/large.jpg');
});

it('handles different url formats', function (): void {
    $file = Mockery::mock(File::class);
    $file->shouldReceive('getUserId')->andReturn(1);
    $file->shouldReceive('getImageUrls')->andReturn([
        'original' => 'https://cdn.example.com/orig.png',
        'small'    => 'https://cdn.example.com/sm.png',
        'medium'   => 'https://cdn.example.com/md.png',
        'large'    => 'https://cdn.example.com/lg.png',
    ]);

    $response = ChangeAvatarResponse::build($file);

    expect($response->original)->toBe('https://cdn.example.com/orig.png')
        ->and($response->small)->toBe('https://cdn.example.com/sm.png');
});

it('handles different user ids', function (): void {
    $file = Mockery::mock(File::class);
    $file->shouldReceive('getUserId')->andReturn(999);
    $file->shouldReceive('getImageUrls')->andReturn([
        'original' => '/test.jpg',
        'small'    => '/test.jpg',
        'medium'   => '/test.jpg',
        'large'    => '/test.jpg',
    ]);

    $response = ChangeAvatarResponse::build($file);

    expect($response->userId)->toBe(999);
});
