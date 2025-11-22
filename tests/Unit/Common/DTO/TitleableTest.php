<?php

declare(strict_types=1);

namespace App\Tests\Unit\Common\DTO;

use App\Common\DTO\Titleable;

// Test implementation of Titleable interface
class TestTitleable implements Titleable
{
    public function __construct(
        private string $title
    ) {
    }

    public function getTitle(): string
    {
        return $this->title;
    }
}

// Test implementation with dynamic title
class DynamicTitleable implements Titleable
{
    public function __construct(
        private string $prefix,
        private string $name
    ) {
    }

    public function getTitle(): string
    {
        return $this->prefix . ': ' . $this->name;
    }
}

it('implements titleable interface correctly', function (): void {
    $titleable = new TestTitleable('Test Title');

    expect($titleable)->toBeInstanceOf(Titleable::class);
    expect($titleable->getTitle())->toBe('Test Title');
});

it('can return different titles', function (): void {
    $titleable1 = new TestTitleable('First Title');
    $titleable2 = new TestTitleable('Second Title');

    expect($titleable1->getTitle())->toBe('First Title');
    expect($titleable2->getTitle())->toBe('Second Title');
    expect($titleable1->getTitle())->not->toBe($titleable2->getTitle());
});

it('can return dynamic title', function (): void {
    $dynamic = new DynamicTitleable('User', 'John Doe');

    expect($dynamic->getTitle())->toBe('User: John Doe');
});

it('can return empty title', function (): void {
    $emptyTitleable = new TestTitleable('');

    expect($emptyTitleable->getTitle())->toBe('');
});

it('can return title with special characters', function (): void {
    $specialTitleable = new TestTitleable('Название с русскими символами & спецсимволы');

    expect($specialTitleable->getTitle())->toBe('Название с русскими символами & спецсимволы');
});
