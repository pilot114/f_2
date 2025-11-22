<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\User\Cabinet\Entity;

use App\Domain\Portal\Cabinet\Entity\Response;

it('creates response with id and name', function (): void {
    $response = new Response(id: 1, name: 'Test Response');

    expect($response->id)->toBe(1)
        ->and($response->name)->toBe('Test Response');
});

it('converts to array', function (): void {
    $response = new Response(id: 42, name: 'My Response');

    $result = $response->toArray();

    expect($result)->toBe([
        'id'   => 42,
        'name' => 'My Response',
    ]);
});

it('handles cyrillic characters', function (): void {
    $response = new Response(id: 10, name: 'Ответ пользователя');

    expect($response->name)->toBe('Ответ пользователя')
        ->and($response->toArray()['name'])->toBe('Ответ пользователя');
});

it('handles empty name', function (): void {
    $response = new Response(id: 5, name: '');

    expect($response->name)->toBe('')
        ->and($response->toArray()['name'])->toBe('');
});

it('handles different ids', function (int $id): void {
    $response = new Response(id: $id, name: 'Response');

    expect($response->id)->toBe($id);
})->with([1, 100, 999, 123456]);

it('toArray contains all fields', function (): void {
    $response = new Response(id: 1, name: 'Test');

    $result = $response->toArray();

    expect($result)->toHaveKeys(['id', 'name'])
        ->and($result)->toHaveCount(2);
});

it('fields are readonly', function (): void {
    $response = new Response(id: 1, name: 'Test');

    expect($response)->toBeInstanceOf(Response::class);
});
