<?php

declare(strict_types=1);

namespace App\System\RPC\DTO;

use App\System\Exception\BadRequestHttpExceptionWithViolations;
use App\System\RPC\Exception\RpcTypeException;
use BackedEnum;
use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Source\Source;
use CuyZ\Valinor\Mapper\Tree\Message\Messages;
use CuyZ\Valinor\MapperBuilder;
use DateTimeImmutable;
use ReflectionFunctionAbstract;
use ReflectionNamedType;
use ReflectionParameter;
use Stringable;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentResolverInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RpcArgumentResolver implements ArgumentResolverInterface
{
    /** @var list<ConstraintViolation> */
    private array $violations = [];

    public function getArguments(
        Request $request,
        callable $controller,
        ?ReflectionFunctionAbstract $reflector = null,
    ): array {
        if (!$reflector instanceof ReflectionFunctionAbstract || !is_array($controller)) {
            return [];
        }
        $className = $controller[0]::class;

        $doc = $reflector->getDocComment();
        $docBlockParser = is_string($doc) ? new DocBlockParser($doc) : null;

        $rpcParams = (array) $request->attributes->get('rpc_params', []);
        /** @var ValidatorInterface $validator */
        $validator = $request->attributes->get('validator');

        /** @var bool $isAutomapped */
        $isAutomapped = $request->attributes->get('is_automapped');
        if ($isAutomapped) {
            return $this->autoMap($reflector, $rpcParams, $validator);
        }

        $arguments = [];
        foreach ($reflector->getParameters() as $parameter) {
            if ($parameter->isVariadic()) {
                return $rpcParams; // для variadic передаем как есть
            }

            $paramName = $parameter->getName();

            if (!isset($rpcParams[$paramName])) {
                $arguments[$paramName] = $this->handleMissingParameter($parameter, $paramName);
                continue;
            }

            $value = $rpcParams[$paramName];

            $this->validateAsserts($parameter, $paramName, $value, $validator);

            // типизированная коллекция, определенная через phpDoc
            if ($docBlockParser instanceof DocBlockParser && is_array($value)) {
                $dtoName = $docBlockParser->getArrayType($className, $parameter->name);
                if ($dtoName !== null) {
                    $arguments[$paramName] = array_map(
                        fn ($x) => is_array($x) ? $this->handleClassParameter($dtoName, $x, $validator) : $x,
                        $value,
                    );
                    continue;
                }
            }

            $type = $parameter->getType();

            if ($type === null) {
                throw new RpcTypeException("Не указан тип (параметр: $paramName)");
            }
            if ($type::class !== ReflectionNamedType::class) {
                throw new RpcTypeException("Не поддерживается объединение / пересечение типов (параметр: $paramName)");
            }

            if ($type->isBuiltin()) {
                $arguments[$paramName] = $this->handleBuiltin($type, $paramName, $value);
                continue;
            }

            /** @var class-string $typeClassName */
            $typeClassName = $type->getName();

            if (enum_exists($typeClassName) && (is_int($value) || is_string($value))) {
                if (is_subclass_of($typeClassName, BackedEnum::class)) {
                    $arguments[$paramName] = $typeClassName::tryFrom($value);
                    continue;
                }
                foreach ($typeClassName::cases() as $case) {
                    if ($case->name === $value) {
                        $arguments[$paramName] = constant("$typeClassName::$value");
                        continue 2;
                    }
                }
            }

            $arguments[$paramName] = $this->handleClassParameter($typeClassName, $value, $validator);
        }

        if ($this->violations !== []) {
            throw new BadRequestHttpExceptionWithViolations(
                new ConstraintViolationList($this->violations)
            );
        }

        return $arguments;
    }

    private function validateAsserts(
        ReflectionParameter $parameter,
        string $parameterName,
        mixed $value,
        ValidatorInterface $validator
    ): void {
        foreach ($parameter->getAttributes() as $attribute) {
            if (!str_starts_with($attribute->getName(), 'Symfony\Component\Validator\Constraints')) {
                continue;
            }
            /** @var Constraint $constraint */
            $constraint = new ($attribute->getName())(...$attribute->getArguments());

            foreach ($validator->validate($value, $constraint) as $error) {
                $this->addViolation($parameterName, $error->getMessage(), $parameterName);
            }
        }
    }

    /**
     * @param class-string $className
     */
    private function handleClassParameter(string $className, mixed $value, ValidatorInterface $validator): ?object
    {
        $isDate = in_array($className, ['DateTimeImmutable', 'DateTime'], true);
        if ($isDate && is_string($value)) {
            return new DateTimeImmutable($value);
        }

        if (!is_array($value)) {
            return null;
        }

        // валидация маппинга
        try {
            $dto = $this->dtoResolve($className, $value);
        } catch (MappingError $e) {
            $this->mappingErrorFormat($e, $className);
            return null;
        }

        // кастомная валидация по аннотациям
        foreach ($validator->validate($dto) as $violation) {
            $this->addViolation($violation->getPropertyPath(), $violation->getMessage());
        }

        return $dto;
    }

    private function handleBuiltin(ReflectionNamedType $type, string $parameterName, mixed $value): mixed
    {
        $actual = gettype($value);
        /* https://bugs.php.net/bug.php?id=74742 */
        $actual = match ($actual) {
            'integer' => 'int',
            'double'  => 'float',
            'boolean' => 'bool',
            default   => $actual
        };
        $expected = $type->getName();
        if ($actual === $expected) {
            return $value;
        }
        $this->addViolation($parameterName, "неправильный тип ($actual вместо $expected)");
        return null;
    }

    private function handleMissingParameter(ReflectionParameter $parameter, string $parameterName): mixed
    {
        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }
        if ($parameter->allowsNull()) {
            return null;
        }
        $this->addViolation($parameterName, 'Не передан обязательный параметр');
        return null;
    }

    private function mappingErrorFormat(MappingError $e, string $dtoName): void
    {
        $parts = explode('\\', $dtoName);
        $root = $parts[array_key_last($parts)];

        $formatter = new RussianMessageFormatter();
        $errorMessages = Messages::flattenFromNode($e->node())->errors();

        foreach ($errorMessages as $message) {
            foreach ($message->node()->messages() as $info) {
                $info = $formatter->format($info);
                $this->addViolation($info->node()->path(), $info->toString(), $root);
            }
        }
    }

    private function addViolation(string $path, string|Stringable $message, string $root = ''): void
    {
        $this->violations[] = new ConstraintViolation(
            $message, // Сообщение об ошибке
            null,     // Шаблон сообщения (если используется)
            [],       // Параметры сообщения
            $root,    // Корневой объект
            $path,    // Путь к свойству
            null      // Недопустимое значение
        );
    }

    /**
     * @template T of object
     * @param class-string<T> $dtoName
     * @return T
     * @throws MappingError
     */
    private function dtoResolve(string $dtoName, array $raw): object
    {
        return (new MapperBuilder())
            ->mapper()
            ->map($dtoName, Source::iterable(
                new EnumCastSource($raw, $dtoName)
            ));
    }

    private function autoMap(ReflectionFunctionAbstract $reflector, array $rpcParams, ValidatorInterface $validator): array
    {
        if (count($reflector->getParameters()) > 1) {
            throw new RpcTypeException("При автомаппинге должен быть только один параметр");
        }

        $param = $reflector->getParameters()[0] ?? null;
        if ($param === null) {
            return [];
        }

        $type = $param->getType();
        $paramName = $param->getName();

        if ($type === null) {
            throw new RpcTypeException("Не указан тип (параметр: $paramName)");
        }
        if ($type::class !== ReflectionNamedType::class) {
            throw new RpcTypeException("Не поддерживается объединение / пересечение типов (параметр: $paramName)");
        }
        /** @var class-string $typeClassName */
        $typeClassName = $type->getName();

        $result = [
            $param->getName() => $this->handleClassParameter($typeClassName, $rpcParams, $validator),
        ];

        if ($this->violations !== []) {
            throw new BadRequestHttpExceptionWithViolations(
                new ConstraintViolationList($this->violations)
            );
        }

        return $result;
    }
}
