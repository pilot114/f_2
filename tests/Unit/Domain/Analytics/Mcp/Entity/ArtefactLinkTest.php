<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Analytics\Mcp\Entity;

use App\Domain\Analytics\Mcp\Entity\Artefact;
use App\Domain\Analytics\Mcp\Entity\ArtefactLink;
use App\Domain\Analytics\Mcp\Enum\ArtefactType;

it('creates artefact link with id and artefact', function (): void {
    $artefact = new Artefact(
        id: 1,
        name: 'test_table',
        type: ArtefactType::TABLE,
        content: serialize([
            'data' => 'test',
        ]),
    );

    $link = new ArtefactLink(
        id: 100,
        artefact: $artefact,
    );

    $result = $link->toArray();

    expect($result['id'])->toBe(100)
        ->and($result['artefact'])->toBeArray()
        ->and($result['artefact']['id'])->toBe(1)
        ->and($result['artefact']['name'])->toBe('test_table');
});

it('converts to array with nested artefact', function (): void {
    $artefact = new Artefact(
        id: 42,
        name: 'users_view',
        type: ArtefactType::VIEW,
        content: serialize([
            'columns' => ['id', 'name'],
        ]),
    );

    $link = new ArtefactLink(
        id: 200,
        artefact: $artefact,
    );

    $result = $link->toArray();

    expect($result)->toHaveKeys(['id', 'artefact'])
        ->and($result['artefact'])->toHaveKeys(['id', 'name', 'type', 'content']);
});

it('handles artefact with different types', function (ArtefactType $type): void {
    $artefact = new Artefact(
        id: 1,
        name: 'test_artefact',
        type: $type,
        content: serialize([
            'test' => 'data',
        ]),
    );

    $link = new ArtefactLink(
        id: 1,
        artefact: $artefact,
    );

    $result = $link->toArray();

    expect($result['artefact']['type'])->toBe($type);
})->with([
    ArtefactType::TABLE,
    ArtefactType::VIEW,
    ArtefactType::PROCEDURE,
]);

it('toArray contains correct structure', function (): void {
    $artefact = new Artefact(
        id: 10,
        name: 'procedure_name',
        type: ArtefactType::PROCEDURE,
        content: serialize([
            'params' => [],
        ]),
    );

    $link = new ArtefactLink(
        id: 500,
        artefact: $artefact,
    );

    $result = $link->toArray();

    expect($result)->toHaveCount(2)
        ->and($result['id'])->toBeInt()
        ->and($result['artefact'])->toBeArray();
});

it('handles different link ids', function (int $id): void {
    $artefact = new Artefact(
        id: 1,
        name: 'test',
        type: ArtefactType::TABLE,
        content: serialize([]),
    );

    $link = new ArtefactLink(
        id: $id,
        artefact: $artefact,
    );

    $result = $link->toArray();

    expect($result['id'])->toBe($id);
})->with([1, 100, 999, 12345]);
