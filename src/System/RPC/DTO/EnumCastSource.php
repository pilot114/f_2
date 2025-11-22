<?php

declare(strict_types=1);

namespace App\System\RPC\DTO;

use BackedEnum;
use CuyZ\Valinor\Mapper\Source\Source;
use Generator;
use InvalidArgumentException;
use IteratorAggregate;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionUnionType;

/**
 * настройка для valinor по обработке enum
 *
 * @implements IteratorAggregate<string, mixed>
 */
class EnumCastSource implements IteratorAggregate
{
    /** @var array<string, ReflectionNamedType[]> */
    private array $types;

    /**
     * @param array<string, mixed> $source
     * @param class-string $dtoName
     */
    public function __construct(
        private array $source,
        string $dtoName,
    ) {
        $ref = new ReflectionClass($dtoName);
        foreach ($ref->getProperties() as $property) {
            $type = $property->getType();
            if ($type instanceof ReflectionNamedType) {
                $this->types[$property->getName()][] = $type;
            }
            if ($type instanceof ReflectionUnionType) {
                foreach ($type->getTypes() as $type) {
                    if ($type instanceof ReflectionNamedType) {
                        $this->types[$property->getName()][] = $type;
                    }
                }
            }
        }
    }

    public function getIterator(): Generator
    {
        foreach ($this->source as $name => &$value) {
            $this->checkEnumIntersects($name);
            foreach ($this->types[$name] as $type) {
                /** @var class-string $typeName */
                $typeName = $type->getName();
                $isBacked = enum_exists($typeName) && in_array('BackedEnum', class_implements($typeName), true);
                if (!$isBacked) {
                    continue;
                }

                /** @var BackedEnum $case */
                foreach ($typeName::cases() as $case) {
                    if ($case->name === $value) {
                        $value = $case->value;
                    }
                }
            }

        }
        yield from Source::iterable($this->source);
    }

    private function checkEnumIntersects(string $argumentName): void
    {
        $enumNames = [];

        foreach ($this->types[$argumentName] as $type) {
            $typeName = $type->getName();
            $isBacked = enum_exists($typeName) && in_array('BackedEnum', class_implements($typeName), true);
            if ($isBacked) {
                foreach ($typeName::cases() as $case) {
                    $enumNames[$case->name][] = $typeName;
                }
            }
        }

        $conflictMessages = [];
        foreach ($enumNames as $name => $types) {
            if (count($types) > 1) {
                $conflictMessages[] = "enumName: '$name' найдено в: " . implode(', ', $types);
            }
        }

        if ($conflictMessages !== []) {
            throw new InvalidArgumentException("Невозможно определить тип $argumentName однозначно. Конфликты: " . implode('; ', $conflictMessages));
        }
    }
}
