<?php

declare(strict_types=1);

namespace App\System\RPC\Attribute;

use App\System\RPC\Exception\RpcResultException;
use App\System\RPC\Exception\RpcTypeException;
use phpDocumentor\Reflection\DocBlock\Tag;
use phpDocumentor\Reflection\DocBlock\Tags\Return_;
use phpDocumentor\Reflection\DocBlock\Tags\TagWithType;
use phpDocumentor\Reflection\DocBlock\Tags\Var_;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\PseudoTypes\ArrayShape;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\AbstractList;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Compound;
use phpDocumentor\Reflection\Types\ContextFactory;
use phpDocumentor\Reflection\Types\Object_;
use PSX\OpenAPI\Schemas;
use ReflectionClass;
use ReflectionEnum;
use ReflectionMethod;
use ReflectionNamedType;

/**
 * Извлечение Json-Schema из DTO и PHP Doc
 */
class JsonSchemaExtractor
{
    private string $currentMethod = '';

    public function __construct(
        private Schemas $schemas = new Schemas()
    ) {
    }

    public function getSchemas(): Schemas
    {
        return $this->schemas;
    }

    /**
     * @param class-string $typeName
     */
    public function buildSchemaByDto(string $typeName): array
    {
        $dtoRef = new ReflectionClass($typeName);

        $required = [];
        $properties = [];
        foreach ($dtoRef->getProperties() as $property) {
            $type = $property->getType();
            if ($type === null) {
                continue;
            }
            if ($type::class !== ReflectionNamedType::class) {
                continue;
            }
            if (!$type->allowsNull() && !$property->hasDefaultValue()) {
                $required[] = $property->getName();
            }
            /**
             * @var class-string $typeName
             */
            $typeName = $type->getName();

            if (enum_exists($typeName)) {
                $reflectionEnum = new ReflectionEnum($typeName);
                $backingType = $reflectionEnum->getBackingType();
                if (!$backingType instanceof ReflectionNamedType) {
                    continue;
                }

                $cases = [];
                foreach ($reflectionEnum->getCases() as $case) {
                    $enum = $case->getValue();
                    $cases[] = $enum->name;
                }

                $schema = [
                    'type' => 'string',
                    'enum' => $cases,
                ];
            } elseif ($type->isBuiltin()) {
                if ($type->getName() === 'array' && $property->getDocComment() !== false) {
                    $schema = $this->buildSchemaByDtoAndPhpDoc($dtoRef, $property->getDocComment());
                } else {
                    $schema = $this->getSchemaByType($type);
                }
            } else {
                $typeName = ltrim($typeName, '\\');
                $schemaName = str_replace('\\', '_', $typeName);

                $existing = $this->schemas->get($schemaName);
                if ($existing === null) {
                    $schema = $this->buildSchemaByDto($typeName);
                    $this->schemas->put($schemaName, $schema);
                }
                $schema = [
                    '$ref' => '#/components/schemas/' . $schemaName,
                ];
            }

            $properties[$property->getName()] = $schema;
        }

        $isDate = in_array($typeName, ['DateTimeImmutable', 'DateTime'], true);
        if ($isDate) {
            return [
                'type'   => 'string',
                'format' => 'date-time',
            ];
        }

        return [
            'type'       => 'object',
            'required'   => $required,
            'properties' => $properties,
        ];
    }

    /**
     * @param ReflectionClass<object> $dtoRef
     */
    public function buildSchemaByDtoAndPhpDoc(ReflectionClass $dtoRef, string $phpDoc): array
    {
        $docBlockFactory = DocBlockFactory::createInstance();
        $docBlock = $docBlockFactory->create($phpDoc, (new ContextFactory())->createFromReflector($dtoRef));
        /** @var Var_[] $varTags */
        $varTags = $docBlock->getTagsByName('var');

        if ($varTags === []) {
            throw new RpcTypeException("Для {$dtoRef->getName()} не определен тип данных");
        }
        $varType = $varTags[0]->getType();

        if ($varType instanceof ArrayShape) {
            return $this->resolveArrayShapeScheme($varType);
        }
        if ($varType instanceof Array_) {
            return $this->resolveArrayScheme($varType);
        }

        throw new RpcTypeException("Для {$dtoRef->getName()} не определен тип данных");
    }

    protected function resolveArrayScheme(Array_ $varType): array
    {
        $valueType = $varType->getValueType();

        if ($valueType instanceof Object_) {
            /** @var class-string $className */
            $className = ltrim((string) $valueType, '\\');

            $schemaName = str_replace('\\', '_', $className);

            $existing = $this->schemas->get($schemaName);
            if ($existing === null) {
                $schema = $this->buildSchemaByDto($className);
                $this->schemas->put($schemaName, $schema);
            }
            return [
                'type'  => 'array',
                'items' => [
                    '$ref' => '#/components/schemas/' . $schemaName,
                ],
            ];
        }

        if ($valueType instanceof ArrayShape) {
            return [
                'type'  => 'array',
                'items' => $this->resolveArrayShapeScheme($valueType),
            ];
        }

        if ($valueType instanceof Array_) {
            return [
                'type'  => 'array',
                'items' => $this->resolveArrayScheme($valueType),
            ];
        }
        return [
            'type'  => 'array',
            'items' => [
                'type' => $this->typeMap((string) $valueType),
            ],
        ];
    }

    protected function resolveArrayShapeScheme(ArrayShape $arrayShape): array
    {
        $itemsSchema = [
            'type'       => 'object',
            'properties' => [],
            'required'   => [],
        ];

        foreach ($arrayShape->getItems() as $keyValuePair) {
            $key = $keyValuePair->getKey();
            $valueType = $keyValuePair->getValue();

            if ($valueType instanceof Object_) {
                /** @var class-string $className */
                $className = ltrim((string) $valueType, '\\');

                $schemaName = str_replace('\\', '_', $className);

                $existing = $this->schemas->get($schemaName);
                if ($existing === null) {
                    $schema = $this->buildSchemaByDto($className);
                    $this->schemas->put($schemaName, $schema);
                }
                $itemsSchema['properties'][$key] = [
                    'type'  => 'array',
                    'items' => [
                        '$ref' => '#/components/schemas/' . $schemaName,
                    ],
                ];
            } elseif ($valueType instanceof ArrayShape) {
                $itemsSchema['properties'][$key] = $this->resolveArrayShapeScheme($valueType);
            } elseif ($valueType instanceof Array_) {
                $itemsSchema['properties'][$key] = $this->resolveArrayScheme($valueType);
            } else {
                $itemsSchema['properties'][$key] = [
                    'type' => $this->typeMap($valueType->__toString()),
                ];
            }

            if (!$keyValuePair->isOptional()) {
                $itemsSchema['required'][] = $key;
            }
        }

        return $itemsSchema;
    }

    protected function typeMap(string $type): string
    {
        if ($type === 'mixed' && $this->currentMethod !== '') {
            throw new RpcResultException("Не удалось определить возвращаемый тип в RPC методе '{$this->currentMethod}'");
        }
        return self::phpTypeToJsonType($type);
    }

    public static function phpTypeToJsonType(string $type): string
    {
        return match ($type) {
            'int'   => 'integer',
            'float' => 'number',
            'bool', 'true', 'false' => 'boolean',
            'void'  => 'null',
            default => $type,
        };
    }

    public function tryGetGenericType(string $docComment, ReflectionMethod $refMethod, string $genericName): ?string
    {
        /** @var ?TagWithType $tag */
        $tag = $this->getReturnTag($docComment, $refMethod);
        if ($tag === null) {
            return null;
        }
        /** @var ?Type $type */
        $type = $tag->getType();
        if ($type === null) {
            return null;
        }
        if (!$type instanceof AbstractList) {
            return null;
        }
        $object = $type->getValueType();

        // TODO: support union types
        if ($object instanceof Compound) {
            $object = $object->get(0);
        }

        if (!$object instanceof Object_) {
            return null;
        }

        // @phpstan-ignore-next-line
        if ($genericName !== trim((string) $type->getFqsen(), '\\')) {
            return null;
        }
        return trim((string) $object->getFqsen(), '\\');
    }

    protected function getReturnTag(string $docComment, ReflectionMethod $refMethod): ?Tag
    {
        $docBlockFactory = DocBlockFactory::createInstance();
        $docBlock = $docBlockFactory->create($docComment, (new ContextFactory())->createFromReflector($refMethod));
        return $docBlock->getTagsByName('return')[0] ?? null;
    }

    public function getSchemaForReturn(
        ReflectionNamedType $type,
        string $docComment,
        ReflectionMethod $refMethod,
        string $methodName
    ): array {
        $this->currentMethod = $methodName;

        $typeMapped = $this->typeMap($type->getName());

        if ($typeMapped !== 'array') {
            return [
                'type' => $typeMapped,
            ];
        }

        if ($docComment === '') {
            throw new RpcResultException("Нетипизированный result в методе $methodName");
        }

        /** @var ?Return_ $returnTag */
        $returnTag = $this->getReturnTag($docComment, $refMethod);
        $returnType = $returnTag?->getType();

        $result = null;
        if ($returnType instanceof ArrayShape) {
            $result = $this->resolveArrayShapeScheme($returnType);
        }
        if ($returnType instanceof Array_) {
            $result = $this->resolveArrayScheme($returnType);
        }
        if ($result !== null) {
            $this->currentMethod = '';
            return $result;
        }

        throw new RpcResultException("Нетипизированный result в методе $methodName");
    }

    public function getSchemaByType(ReflectionNamedType $type): array
    {
        $typeMapped = $this->typeMap($type->getName());

        if ($typeMapped !== 'array') {
            return [
                'type' => $typeMapped,
            ];
        }

        return [
            'type'  => 'array',
            'items' => [
                'type' => 'string',
            ],
        ];
    }
}
