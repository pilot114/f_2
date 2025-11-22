<?php

declare(strict_types=1);

namespace App\Tests\Unit\System\RPC\DTO;

use App\System\RPC\DTO\EnumCastSource;
use IteratorAggregate;

enum TestStatusEnum: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Pending = 'pending';
}

enum TestTypeEnum: string
{
    case TypeA = 'type_a';
    case TypeB = 'type_b';
}

readonly class TestDTO
{
    public function __construct(
        public TestStatusEnum $status,
        public TestTypeEnum $type,
    ) {
    }
}

readonly class TestUnionDTO
{
    public function __construct(
        public TestStatusEnum|TestTypeEnum $field,
    ) {
    }
}

it('реализует интерфейс IteratorAggregate', function (): void {
    $source = new EnumCastSource([
        'status' => 'Active',
    ], TestDTO::class);

    expect($source)->toBeInstanceOf(IteratorAggregate::class);
});

it('преобразует имя enum в значение для backed enum', function (): void {
    $source = new EnumCastSource([
        'status' => 'Active',
        'type'   => 'TypeA',
    ], TestDTO::class);

    $result = iterator_to_array($source->getIterator());

    expect($result)->toBeArray()
        ->toHaveKey('status')
        ->toHaveKey('type')
        ->and($result['status'])->toBe('active')
        ->and($result['type'])->toBe('type_a');
});

it('не изменяет значение если оно уже является значением enum', function (): void {
    $source = new EnumCastSource([
        'status' => 'active',
        'type'   => 'type_a',
    ], TestDTO::class);

    $result = iterator_to_array($source->getIterator());

    expect($result)->toBeArray()
        ->and($result['status'])->toBe('active')
        ->and($result['type'])->toBe('type_a');
});

it('обрабатывает union типы', function (): void {
    $source = new EnumCastSource([
        'field' => 'Active',
    ], TestUnionDTO::class);

    $result = iterator_to_array($source->getIterator());

    expect($result)->toBeArray()
        ->and($result['field'])->toBe('active');
});

it('не бросает исключение для не-backed enum', function (): void {
    enum SimpleEnum
    {
        case CaseA;
        case CaseB;
    }

    readonly class SimpleDTOTest
    {
        public function __construct(
            public SimpleEnum $value,
        ) {
        }
    }

    $source = new EnumCastSource([
        'value' => 'CaseA',
    ], SimpleDTOTest::class);

    expect(fn (): array => iterator_to_array($source->getIterator()))->not->toThrow(Exception::class);
});

it('корректно обрабатывает множественные поля с разными enum', function (): void {
    $source = new EnumCastSource(
        [
            'status' => 'Active',
            'type'   => 'TypeB',
        ],
        TestDTO::class
    );

    $result = iterator_to_array($source->getIterator());

    expect($result['status'])->toBe('active')
        ->and($result['type'])->toBe('type_b');
});
