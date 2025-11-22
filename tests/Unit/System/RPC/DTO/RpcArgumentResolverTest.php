<?php

declare(strict_types=1);

namespace App\Tests\Unit\System\RPC\DTO;

use App\System\Exception\BadRequestHttpExceptionWithViolations;
use App\System\RPC\DTO\RpcArgumentResolver;
use App\System\RPC\Exception\RpcTypeException;
use DateTimeImmutable;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use ReflectionClass;
use ReflectionMethod;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;

uses(MockeryPHPUnitIntegration::class);

// Test controller class
class TestRpcController
{
    public function intMethod(int $id): void
    {
    }

    public function stringMethod(string $name): void
    {
    }

    public function boolMethod(bool $active): void
    {
    }

    public function floatMethod(float $price): void
    {
    }

    public function arrayMethod(array $data): void
    {
    }

    public function defaultValueMethod(int $id = 10): void
    {
    }

    public function nullableMethod(?int $id): void
    {
    }

    public function requiredMethod(int $id): void
    {
    }

    public function dateTimeMethod(DateTimeImmutable $date): void
    {
    }

    public function variadicMethod(int ...$ids): void
    {
    }

    public function multipleParams(int $id, string $name, bool $active = true): void
    {
    }

    public function nullableStringMethod(?string $name): void
    {
    }

    public function noTypeMethod($noType): void
    {
    }

    public function autoMapEmptyMethod(): void
    {
    }
}

beforeEach(function (): void {
    $this->resolver = new RpcArgumentResolver();
    $this->validator = Mockery::mock(ValidatorInterface::class);
    $this->validator->shouldReceive('validate')->andReturn([])->byDefault();
    $this->controller = new TestRpcController();
});

afterEach(function (): void {
    Mockery::close();
});

it('returns empty array for non-array controller', function (): void {
    $request = new Request();
    $callable = fn (): null => null;
    $result = $this->resolver->getArguments($request, $callable);

    expect($result)->toBe([]);
});

it('returns empty array when reflector is null', function (): void {
    $request = new Request();
    $result = $this->resolver->getArguments($request, [$this->controller, 'intMethod'], null);

    expect($result)->toBe([]);
});

it('handles int parameter correctly', function (): void {
    $reflector = new ReflectionMethod($this->controller, 'intMethod');

    $request = new Request();
    $request->attributes->set('rpc_params', [
        'id' => 123,
    ]);
    $request->attributes->set('validator', $this->validator);
    $request->attributes->set('is_automapped', false);

    $result = $this->resolver->getArguments(
        $request,
        [$this->controller, 'intMethod'],
        $reflector
    );

    expect($result)->toBe([
        'id' => 123,
    ]);
});

it('handles string parameter correctly', function (): void {
    $reflector = new ReflectionMethod($this->controller, 'stringMethod');

    $request = new Request();
    $request->attributes->set('rpc_params', [
        'name' => 'test',
    ]);
    $request->attributes->set('validator', $this->validator);
    $request->attributes->set('is_automapped', false);

    $result = $this->resolver->getArguments(
        $request,
        [$this->controller, 'stringMethod'],
        $reflector
    );

    expect($result)->toBe([
        'name' => 'test',
    ]);
});

it('handles bool parameter correctly', function (): void {
    $reflector = new ReflectionMethod($this->controller, 'boolMethod');

    $request = new Request();
    $request->attributes->set('rpc_params', [
        'active' => true,
    ]);
    $request->attributes->set('validator', $this->validator);
    $request->attributes->set('is_automapped', false);

    $result = $this->resolver->getArguments(
        $request,
        [$this->controller, 'boolMethod'],
        $reflector
    );

    expect($result)->toBe([
        'active' => true,
    ]);
});

it('handles float parameter correctly', function (): void {
    $reflector = new ReflectionMethod($this->controller, 'floatMethod');

    $request = new Request();
    $request->attributes->set('rpc_params', [
        'price' => 99.99,
    ]);
    $request->attributes->set('validator', $this->validator);
    $request->attributes->set('is_automapped', false);

    $result = $this->resolver->getArguments(
        $request,
        [$this->controller, 'floatMethod'],
        $reflector
    );

    expect($result)->toBe([
        'price' => 99.99,
    ]);
});

it('handles array parameter correctly', function (): void {
    $reflector = new ReflectionMethod($this->controller, 'arrayMethod');

    $request = new Request();
    $request->attributes->set('rpc_params', [
        'data' => [1, 2, 3],
    ]);
    $request->attributes->set('validator', $this->validator);
    $request->attributes->set('is_automapped', false);

    $result = $this->resolver->getArguments(
        $request,
        [$this->controller, 'arrayMethod'],
        $reflector
    );

    expect($result)->toBe([
        'data' => [1, 2, 3],
    ]);
});

it('handles missing parameter with default value', function (): void {
    $reflector = new ReflectionMethod($this->controller, 'defaultValueMethod');

    $request = new Request();
    $request->attributes->set('rpc_params', []);
    $request->attributes->set('validator', $this->validator);
    $request->attributes->set('is_automapped', false);

    $result = $this->resolver->getArguments(
        $request,
        [$this->controller, 'defaultValueMethod'],
        $reflector
    );

    expect($result)->toBe([
        'id' => 10,
    ]);
});

it('handles missing nullable parameter', function (): void {
    $reflector = new ReflectionMethod($this->controller, 'nullableMethod');

    $request = new Request();
    $request->attributes->set('rpc_params', []);
    $request->attributes->set('validator', $this->validator);
    $request->attributes->set('is_automapped', false);

    $result = $this->resolver->getArguments(
        $request,
        [$this->controller, 'nullableMethod'],
        $reflector
    );

    expect($result)->toBe([
        'id' => null,
    ]);
});

it('throws exception for missing required parameter', function (): void {
    $reflector = new ReflectionMethod($this->controller, 'requiredMethod');

    $request = new Request();
    $request->attributes->set('rpc_params', []);
    $request->attributes->set('validator', $this->validator);
    $request->attributes->set('is_automapped', false);

    expect(fn () => $this->resolver->getArguments(
        $request,
        [$this->controller, 'requiredMethod'],
        $reflector
    ))->toThrow(BadRequestHttpExceptionWithViolations::class);
});

it('throws exception for wrong type', function (): void {
    $reflector = new ReflectionMethod($this->controller, 'intMethod');

    $request = new Request();
    $request->attributes->set('rpc_params', [
        'id' => 'not-a-number',
    ]);
    $request->attributes->set('validator', $this->validator);
    $request->attributes->set('is_automapped', false);

    expect(fn () => $this->resolver->getArguments(
        $request,
        [$this->controller, 'intMethod'],
        $reflector
    ))->toThrow(BadRequestHttpExceptionWithViolations::class);
});

it('throws exception for parameter without type', function (): void {
    $reflector = new ReflectionMethod($this->controller, 'noTypeMethod');

    $request = new Request();
    $request->attributes->set('rpc_params', [
        'noType' => 123,
    ]);
    $request->attributes->set('validator', $this->validator);
    $request->attributes->set('is_automapped', false);

    expect(fn () => $this->resolver->getArguments(
        $request,
        [$this->controller, 'noTypeMethod'],
        $reflector
    ))->toThrow(RpcTypeException::class, 'Не указан тип');
});

it('handles DateTimeImmutable parameter', function (): void {
    $reflector = new ReflectionMethod($this->controller, 'dateTimeMethod');

    $request = new Request();
    $request->attributes->set('rpc_params', [
        'date' => '2024-01-15 10:00:00',
    ]);
    $request->attributes->set('validator', $this->validator);
    $request->attributes->set('is_automapped', false);

    $result = $this->resolver->getArguments(
        $request,
        [$this->controller, 'dateTimeMethod'],
        $reflector
    );

    expect($result['date'])->toBeInstanceOf(DateTimeImmutable::class)
        ->and($result['date']->format('Y-m-d'))->toBe('2024-01-15');
});

it('handles variadic parameters', function (): void {
    $reflector = new ReflectionMethod($this->controller, 'variadicMethod');

    $request = new Request();
    $request->attributes->set('rpc_params', [1, 2, 3, 4]);
    $request->attributes->set('validator', $this->validator);
    $request->attributes->set('is_automapped', false);

    $result = $this->resolver->getArguments(
        $request,
        [$this->controller, 'variadicMethod'],
        $reflector
    );

    expect($result)->toBe([1, 2, 3, 4]);
});

it('returns empty array for automapping without parameters', function (): void {
    $reflector = new ReflectionMethod($this->controller, 'autoMapEmptyMethod');

    $request = new Request();
    $request->attributes->set('rpc_params', []);
    $request->attributes->set('validator', $this->validator);
    $request->attributes->set('is_automapped', true);

    $result = $this->resolver->getArguments(
        $request,
        [$this->controller, 'autoMapEmptyMethod'],
        $reflector
    );

    expect($result)->toBe([]);
});

it('handles multiple parameters', function (): void {
    $reflector = new ReflectionMethod($this->controller, 'multipleParams');

    $request = new Request();
    $request->attributes->set('rpc_params', [
        'id'   => 42,
        'name' => 'Test',
    ]);
    $request->attributes->set('validator', $this->validator);
    $request->attributes->set('is_automapped', false);

    $result = $this->resolver->getArguments(
        $request,
        [$this->controller, 'multipleParams'],
        $reflector
    );

    expect($result)->toBe([
        'id'     => 42,
        'name'   => 'Test',
        'active' => true,
    ]);
});

it('handles null value for nullable parameter', function (): void {
    $reflector = new ReflectionMethod($this->controller, 'nullableStringMethod');

    $request = new Request();
    $request->attributes->set('rpc_params', [
        'name' => null,
    ]);
    $request->attributes->set('validator', $this->validator);
    $request->attributes->set('is_automapped', false);

    $result = $this->resolver->getArguments(
        $request,
        [$this->controller, 'nullableStringMethod'],
        $reflector
    );

    expect($result)->toBe([
        'name' => null,
    ]);
});

it('has getArguments method with correct signature', function (): void {
    $reflection = new ReflectionClass(RpcArgumentResolver::class);
    $method = $reflection->getMethod('getArguments');

    expect($method->isPublic())->toBeTrue();

    $parameters = $method->getParameters();
    expect($parameters)->toHaveCount(3)
        ->and($parameters[0]->getName())->toBe('request')
        ->and($parameters[1]->getName())->toBe('controller')
        ->and($parameters[2]->getName())->toBe('reflector');
});

it('has private helper methods', function (): void {
    $reflection = new ReflectionClass(RpcArgumentResolver::class);

    expect($reflection->hasMethod('validateAsserts'))->toBeTrue()
        ->and($reflection->hasMethod('handleClassParameter'))->toBeTrue()
        ->and($reflection->hasMethod('handleBuiltin'))->toBeTrue()
        ->and($reflection->hasMethod('handleMissingParameter'))->toBeTrue()
        ->and($reflection->hasMethod('autoMap'))->toBeTrue();
});
