<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Events\Rewards\DTO;

use App\Domain\Events\Rewards\DTO\GroupResponse;
use App\Domain\Events\Rewards\Entity\Group;
use App\Domain\Events\Rewards\Enum\GroupType;

it('creates group response with all fields', function (): void {
    $response = new GroupResponse(
        id: 1,
        name: 'Test Group',
    );

    expect($response->id)->toBe(1)
        ->and($response->name)->toBe('Test Group');
});

it('builds from group entity', function (): void {
    $group = new Group(
        id: 5,
        name: 'Sales Team',
        type: GroupType::GROUP,
    );

    $response = GroupResponse::build($group);

    expect($response->id)->toBe(5)
        ->and($response->name)->toBe('Sales Team');
});

it('handles cyrillic names', function (): void {
    $group = new Group(
        id: 1,
        name: 'Команда продаж',
        type: GroupType::GROUP,
    );

    $response = GroupResponse::build($group);

    expect($response->name)->toBe('Команда продаж');
});

it('handles empty name with default', function (): void {
    $group = new Group(
        id: 1,
        name: '',
        type: GroupType::GROUP,
    );

    $response = GroupResponse::build($group);

    expect($response->name)->toBe('Нераспределенные программы');
});

it('handles different ids', function (): void {
    $response = new GroupResponse(id: 999, name: 'Test');

    expect($response->id)->toBe(999);
});

it('handles long names', function (): void {
    $name = 'This is a very long group name that might be used in the system';
    $response = new GroupResponse(id: 1, name: $name);

    expect($response->name)->toBe($name);
});
