<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Dit\Reporter\Dto;

use App\Domain\Dit\Reporter\Dto\ReportResponse;

it('creates report response with required fields', function (): void {
    $items = [
        [
            'id'   => 1,
            'name' => 'Item 1',
        ],
        [
            'id'   => 2,
            'name' => 'Item 2',
        ],
    ];

    $response = new ReportResponse(
        items: $items,
        keyField: 'id',
        detailField: 'details',
        masterField: 'master',
    );

    expect($response->items)->toBe($items)
        ->and($response->keyField)->toBe('id')
        ->and($response->detailField)->toBe('details')
        ->and($response->masterField)->toBe('master')
        ->and($response->total)->toBe(0);
});

it('creates report response with total', function (): void {
    $items = [
        [
            'id'   => 1,
            'name' => 'Item 1',
        ],
        [
            'id'   => 2,
            'name' => 'Item 2',
        ],
        [
            'id'   => 3,
            'name' => 'Item 3',
        ],
    ];

    $response = new ReportResponse(
        items: $items,
        keyField: 'id',
        detailField: 'details',
        masterField: 'master',
        total: 100,
    );

    expect($response->items)->toHaveCount(3)
        ->and($response->total)->toBe(100);
});

it('creates report response with empty items', function (): void {
    $response = new ReportResponse(
        items: [],
        keyField: 'id',
        detailField: 'details',
        masterField: 'master',
    );

    expect($response->items)->toBeEmpty()
        ->and($response->total)->toBe(0);
});

it('creates report response with complex items', function (): void {
    $items = [
        [
            'id'     => 1,
            'name'   => 'Report Item',
            'nested' => [
                'data' => 'value',
            ],
            'count' => 42,
        ],
    ];

    $response = new ReportResponse(
        items: $items,
        keyField: 'id',
        detailField: 'nested',
        masterField: 'parent',
        total: 1,
    );

    expect($response->items)->toHaveCount(1)
        ->and($response->items[0]['nested'])->toBe([
            'data' => 'value',
        ])
        ->and($response->keyField)->toBe('id')
        ->and($response->detailField)->toBe('nested')
        ->and($response->masterField)->toBe('parent');
});

it('handles different field names', function (): void {
    $response = new ReportResponse(
        items: [[
            'user_id' => 1,
        ]],
        keyField: 'user_id',
        detailField: 'user_details',
        masterField: 'parent_user',
        total: 50,
    );

    expect($response->keyField)->toBe('user_id')
        ->and($response->detailField)->toBe('user_details')
        ->and($response->masterField)->toBe('parent_user');
});
