<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\User\Cabinet\Entity;

use App\Domain\Portal\Cabinet\Entity\Response;
use App\Domain\Portal\Cabinet\Entity\User;

it('creates user with all fields', function (): void {
    $user = new User(
        id: 1,
        name: 'John Doe',
        email: 'john@example.com',
    );

    expect($user->id)->toBe(1)
        ->and($user->name)->toBe('John Doe')
        ->and($user->email)->toBe('john@example.com');
});

it('returns null when no responses', function (): void {
    $user = new User(
        id: 1,
        name: 'Test User',
        email: 'test@example.com',
    );

    expect($user->getResponse())->toBeNull();
});

it('returns first response when responses exist', function (): void {
    $response1 = new Response(id: 1, name: 'First Response');
    $response2 = new Response(id: 2, name: 'Second Response');

    $user = new User(
        id: 1,
        name: 'Test User',
        email: 'test@example.com',
        responses: [$response1, $response2],
    );

    expect($user->getResponse())->toBe($response1);
});

it('converts to array without response', function (): void {
    $user = new User(
        id: 10,
        name: 'Jane Smith',
        email: 'jane@example.com',
    );

    $result = $user->toArray();

    expect($result)->toBe([
        'id'       => 10,
        'name'     => 'Jane Smith',
        'email'    => 'jane@example.com',
        'response' => null,
    ]);
});

it('converts to array with response', function (): void {
    $response = new Response(id: 5, name: 'Test Response');

    $user = new User(
        id: 20,
        name: 'Bob Johnson',
        email: 'bob@example.com',
        responses: [$response],
    );

    $result = $user->toArray();

    expect($result['id'])->toBe(20)
        ->and($result['name'])->toBe('Bob Johnson')
        ->and($result['email'])->toBe('bob@example.com')
        ->and($result['response'])->toBeArray()
        ->and($result['response']['id'])->toBe(5)
        ->and($result['response']['name'])->toBe('Test Response');
});

it('handles cyrillic name and email', function (): void {
    $user = new User(
        id: 1,
        name: 'Иван Иванов',
        email: 'ivan@example.ru',
    );

    expect($user->name)->toBe('Иван Иванов')
        ->and($user->email)->toBe('ivan@example.ru');
});

it('toArray structure is correct', function (): void {
    $user = new User(
        id: 1,
        name: 'Test',
        email: 'test@test.com',
    );

    $result = $user->toArray();

    expect($result)->toHaveKeys(['id', 'name', 'email', 'response'])
        ->and($result)->toHaveCount(4);
});

it('returns first response from multiple responses', function (): void {
    $response1 = new Response(id: 10, name: 'Response A');
    $response2 = new Response(id: 20, name: 'Response B');
    $response3 = new Response(id: 30, name: 'Response C');

    $user = new User(
        id: 1,
        name: 'Multi Response User',
        email: 'multi@example.com',
        responses: [$response1, $response2, $response3],
    );

    $response = $user->getResponse();

    expect($response)->not->toBeNull()
        ->and($response->id)->toBe(10);
});

it('fields are readonly', function (): void {
    $user = new User(
        id: 100,
        name: 'Readonly Test',
        email: 'readonly@example.com',
    );

    expect($user->id)->toBe(100);
});
