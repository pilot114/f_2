<?php

declare(strict_types=1);

namespace App\Tests\Unit\Events\Rewards\Entity;

use App\Domain\Events\Rewards\Entity\User;

it('create user', function (): void {
    $user = new User(
        id: 1,
        name: 'Иван Иванов'
    );

    expect($user->id)->toBe(1);
    expect($user->name)->toBe('Иван Иванов');
});

it('converts to array', function (): void {
    $user = new User(
        id: 1,
        name: 'Иван Иванов'
    );

    $result = $user->toArray();

    expect($result['id'])->toBe(1);
    expect($result['name'])->toBe('Иван Иванов');
});

it('gets short name with two words', function (): void {
    $user = new User(
        id: 1,
        name: 'Иван Иванов'
    );

    $shortName = $user->getShortName();

    expect($shortName)->toBe('Иван И.');
});

it('gets short name with three words', function (): void {
    $user = new User(
        id: 1,
        name: 'Петр Петрович Сидоров'
    );

    $shortName = $user->getShortName();

    expect($shortName)->toBe('Петр П.');
});

it('gets short name with single word', function (): void {
    $user = new User(
        id: 1,
        name: 'Мария'
    );

    $shortName = $user->getShortName();

    expect($shortName)->toBe('Мария');
});

it('gets short name with extra spaces', function (): void {
    $user = new User(
        id: 1,
        name: '  Анна   Петровна  '
    );

    $shortName = $user->getShortName();

    expect($shortName)->toBe('Анна П.');
});

it('gets short name with multiple spaces between words', function (): void {
    $user = new User(
        id: 1,
        name: 'Иван    Иванович'
    );

    $shortName = $user->getShortName();

    expect($shortName)->toBe('Иван И.');
});

it('gets short name with empty name', function (): void {
    $user = new User(
        id: 1,
        name: ''
    );

    $shortName = $user->getShortName();

    expect($shortName)->toBe('');
});

it('gets short name with only spaces', function (): void {
    $user = new User(
        id: 1,
        name: '   '
    );

    $shortName = $user->getShortName();

    expect($shortName)->toBe('');
});

it('converts to user response', function (): void {
    $user = new User(
        id: 1,
        name: 'Иван Иванов'
    );

    $result = $user->toUserResponse();

    expect($result->id)->toBe(1);
    expect($result->name)->toBe('Иван Иванов');
});
