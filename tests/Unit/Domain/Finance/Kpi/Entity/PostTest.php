<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Finance\Kpi\Entity;

use App\Domain\Finance\Kpi\Entity\Post;

it('creates post with id and name', function (): void {
    $post = new Post(1, 'Manager');

    expect($post->getId())->toBe(1)
        ->and($post->getName())->toBe('Manager');
});

it('returns correct name', function (): void {
    $post = new Post(2, 'Developer');

    expect($post->getName())->toBe('Developer');
});

it('returns correct id', function (): void {
    $post = new Post(42, 'Analyst');

    expect($post->getId())->toBe(42);
});

it('converts to array', function (): void {
    $post = new Post(10, 'Senior Developer');

    $result = $post->toArray();

    expect($result)->toBe([
        'id'   => 10,
        'name' => 'Senior Developer',
    ]);
});

it('handles cyrillic characters in name', function (): void {
    $post = new Post(5, 'Менеджер по продажам');

    expect($post->getName())->toBe('Менеджер по продажам')
        ->and($post->toArray()['name'])->toBe('Менеджер по продажам');
});

it('handles different post names', function (string $postName): void {
    $post = new Post(1, $postName);

    expect($post->getName())->toBe($postName);
})->with([
    'Junior Developer',
    'Senior Manager',
    'Team Lead',
    'HR Specialist',
    'System Administrator',
]);

it('toArray contains all fields', function (): void {
    $post = new Post(123, 'Test Position');

    $result = $post->toArray();

    expect($result)->toHaveKeys(['id', 'name'])
        ->and($result)->toHaveCount(2);
});
