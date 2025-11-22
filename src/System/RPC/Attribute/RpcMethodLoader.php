<?php

declare(strict_types=1);

namespace App\System\RPC\Attribute;

use App\Common\Attribute\RpcMethod;
use App\Common\Attribute\RpcParam;
use App\Common\DTO\FindResponse;
use App\System\ControllerAttributeLoader;
use App\System\DomainSourceCodeFinder;
use App\System\RPC\Exception\RpcTypeException;
use Generator;
use phpDocumentor\Reflection\DocBlock\Tags\Var_;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\PseudoTypes\ArrayShape;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\ContextFactory;
use phpDocumentor\Reflection\Types\Object_;
use PSX\OpenAPI\Schemas;
use ReflectionClass;
use ReflectionEnum;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionProperty;
use Symfony\Contracts\Cache\CacheInterface;

/**
 * @extends ControllerAttributeLoader<RpcMethod>
 */
class RpcMethodLoader extends ControllerAttributeLoader
{
    private Schemas $schemas;

    public function __construct(
        DomainSourceCodeFinder $fileLoader,
        CacheInterface $cache,
        string $env,
        protected JsonSchemaExtractor $extractor,
    ) {
        parent::__construct($fileLoader, $cache, $env);
        $this->schemas = new Schemas();
    }

    public function getSchemas(): Schemas
    {
        return $this->extractor->getSchemas();
    }

    /**
     * @return Generator<string, RpcMethod>
     */
    public function loadWithFilter(array $tags = [], ?string $method = null): Generator
    {
        foreach ($this->load() as $fqn => $rpc) {
            if ($tags !== [] && array_intersect($tags, $rpc->tags) !== []) {
                yield $fqn => $rpc;
                continue;
            }
            if ($method !== null && $rpc->name === $method) {
                yield $fqn => $rpc;
                continue;
            }
            if ($tags !== []) {
                continue;
            }
            if ($method !== null) {
                continue;
            }
            yield $fqn => $rpc;
        }
    }

    public function getFqnByMethodName(string $methodName): ?string
    {
        foreach ($this->load() as $fqn => $method) {
            if ($method->name === $methodName) {
                return $fqn;
            }
        }
        return null;
    }

    /**
     * @return Generator<string, RpcMethod>
     */
    public function load(): Generator
    {
        /** @var RpcMethod $method */
        foreach ($this->doLoad(RpcMethod::class) as $fqn => $method) {
            $method->fqn = $fqn;
            yield $fqn => $method;
        }
    }

    /**
     * @param RpcMethod $attribute
     */
    protected function prepareAttribute(mixed $attribute, ReflectionMethod $refMethod): mixed
    {
        $docComment = (string) $refMethod->getDocComment();

        $attribute = $this->setParams($attribute, $refMethod);

        if ($returnType = $refMethod->getReturnType()) {
            if (!$returnType instanceof ReflectionNamedType) {
                throw new RpcTypeException('Не поддерживается объединение / пересечение типов в возвращаемом значении');
            }
            return $this->setResult($attribute, $returnType, $docComment, $refMethod);
        }
        return $attribute;
    }

    protected function setParams(RpcMethod $method, ReflectionMethod $refMethod): RpcMethod
    {
        if ($method->isAutomapped) {
            $refParam = $refMethod->getParameters()[0];
            $type = $refParam->getType();
            if (!$type instanceof ReflectionNamedType) {
                return $method;
            }
            /** @var class-string $typeName */
            $typeName = $type->getName();

            $dto = new ReflectionClass($typeName);
            foreach ($dto->getProperties() as $refParam) {
                $rpcParam = $this->getParamByRef($refParam);
                if ($rpcParam instanceof RpcParam) {
                    $method->fqn = "$refMethod->class::$refMethod->name";
                    $method->params[$refParam->name] = $rpcParam;
                }
            }
            return $method;
        }

        foreach ($refMethod->getParameters() as $refParam) {
            $rpcParam = $this->getParamByRef($refParam);
            if ($rpcParam instanceof RpcParam) {
                $method->fqn = "$refMethod->class::$refMethod->name";
                $method->params[$refParam->name] = $rpcParam;
            }
        }

        return $method;
    }

    protected function getSchemaByType(ReflectionNamedType $type): array
    {
        $typeMapped = $this->typeMap($type->getName());

        if ($typeMapped === 'array') {
            return [
                'type'  => 'array',
                'items' => [
                    'type' => 'string', // TODO: получить настоящее значение
                ],
            ];
        }
        return [
            'type' => $typeMapped,
        ];
    }

    protected function getDefaultValueForPromotionProperty(ReflectionProperty $property): array
    {
        /** @var class-string<object> $className */
        $className = $property->class;
        $classRef = new ReflectionClass($className);
        $constructor = $classRef->getConstructor();
        if ($constructor === null) {
            return [false, null];
        }
        foreach ($constructor->getParameters() as $parameter) {
            if ($parameter->name === $property->getName() && $parameter->isDefaultValueAvailable()) {
                return [true, $parameter->getDefaultValue()];
            }
        }
        return [false, null];
    }

    protected function getParamByRef(ReflectionProperty | ReflectionParameter $reflect): ?RpcParam
    {
        $refAttrParam = $reflect->getAttributes(RpcParam::class)[0] ?? null;
        /** @var RpcParam $param */
        $param = $refAttrParam ? $refAttrParam->newInstance() : new RpcParam();
        $type = $reflect->getType();
        if ($type === null) {
            return null;
        }
        if ($type::class !== ReflectionNamedType::class) {
            return null;
        }

        if (!$type->isBuiltin()) {
            /**
             * @var class-string $typeName
             */
            $typeName = $type->getName();
            $param->schema = $this->extractor->buildSchemaByDto($typeName);
            $param->schemaName = $typeName;
        } else {
            $param->schema = $this->extractor->getSchemaByType($type);
        }
        if ($reflect instanceof ReflectionParameter) {
            $param->required = !$reflect->allowsNull() && !$reflect->isDefaultValueAvailable();
        }
        if ($reflect instanceof ReflectionProperty) {
            $type = $reflect->getType();
            if ($type === null) {
                return null;
            }
            [$hasDefault] = $this->getDefaultValueForPromotionProperty($reflect);
            $param->required = !$type->allowsNull() && !$hasDefault;
        }
        return $param;
    }

    protected function setResult(
        RpcMethod $method,
        ReflectionNamedType $type,
        string $docComment,
        ReflectionMethod $refMethod
    ): RpcMethod {
        // Если тип встроенный - пробуем выставить схему из phpDoc (@return)
        if ($type->isBuiltin()) {
            $method->resultSchema = $this->extractor->getSchemaForReturn($type, $docComment, $refMethod, $method->name);
            return $method;
        }

        /**
         * @var class-string $typeName
         */
        $typeName = $type->getName();

        // обработка дженериков
        if ($typeName === FindResponse::class) {
            if ($docComment === '') {
                throw new RpcTypeException("{$method->name}: не задан phpDoc для FindResponse");
            }
            /**
             * @var ?class-string $genericTypeForFindResponse
             */
            $genericTypeForFindResponse = $this->extractor->tryGetGenericType($docComment, $refMethod, $typeName);
            if ($genericTypeForFindResponse === null) {
                throw new RpcTypeException("{$method->name}: не задан тип элемента для FindResponse");
            }
            $method->resultSchema = $this->extractor->buildSchemaByDto($typeName);
            $method->resultSchemaName = $typeName;

            $method->genericTypeSchema = $this->extractor->buildSchemaByDto($genericTypeForFindResponse);
            $method->genericTypeSchemaName = $genericTypeForFindResponse;
            return $method;
        }

        // обработка обычных DTO
        $method->resultSchema = $this->extractor->buildSchemaByDto($typeName);
        $method->resultSchemaName = $typeName;
        return $method;
    }

    /**
     * @param class-string $typeName
     */
    protected function buildSchemaByDto(string $typeName): array
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
            $schema = [
                'type' => 'mixed',
            ];

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
                    $docBlockFactory = DocBlockFactory::createInstance();
                    $docBlock = $docBlockFactory->create($property->getDocComment(), (new ContextFactory())->createFromReflector($dtoRef));
                    /** @var Var_[] $varTags */
                    $varTags = $docBlock->getTagsByName('var');

                    if ($varTags !== []) {
                        $varTag = $varTags[0];
                        $varType = $varTag->getType();

                        if ($varType instanceof ArrayShape) {
                            $schema = $this->resolveArrayShapeScheme($varType);
                        } elseif ($varType instanceof Array_) {
                            $schema = $this->resolveArrayScheme($varType);
                        }
                    }
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
            $schema = [
                'type'  => 'array',
                'items' => [
                    '$ref' => '#/components/schemas/' . $schemaName,
                ],
            ];
        } elseif ($valueType instanceof ArrayShape) {
            $schema = [
                'type'  => 'array',
                'items' => $this->resolveArrayShapeScheme($valueType),
            ];
        } elseif ($valueType instanceof Array_) {
            $schema = [
                'type'  => 'array',
                'items' => $this->resolveArrayScheme($valueType),
            ];
        } else {
            $schema = [
                'type'  => 'array',
                'items' => [
                    'type' => $this->typeMap((string) $valueType),
                ],
            ];
        }

        return $schema;
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
        return JsonSchemaExtractor::phpTypeToJsonType($type);
    }
}
