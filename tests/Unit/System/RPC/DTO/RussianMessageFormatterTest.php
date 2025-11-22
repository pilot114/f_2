<?php

declare(strict_types=1);

namespace App\Tests\Unit\System\RPC\DTO;

use App\System\RPC\DTO\RussianMessageFormatter;
use CuyZ\Valinor\Mapper\Tree\Message\Formatter\MessageFormatter;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use ReflectionClass;

uses(MockeryPHPUnitIntegration::class);

beforeEach(function (): void {
    $this->formatter = new RussianMessageFormatter();
});

it('реализует интерфейс MessageFormatter', function (): void {
    expect($this->formatter)->toBeInstanceOf(MessageFormatter::class);
});

it('имеет массив переводов', function (): void {
    expect(RussianMessageFormatter::TRANSLATIONS)->toBeArray()
        ->not->toBeEmpty();
});

it('все переводы содержат ключ ru', function (): void {
    foreach (RussianMessageFormatter::TRANSLATIONS as $translations) {
        expect($translations)->toHaveKey('ru');
    }
});

it('содержит переводы для основных сообщений об ошибках', function (): void {
    $translations = RussianMessageFormatter::TRANSLATIONS;

    expect($translations)->toHaveKey('Cannot be empty.')
        ->toHaveKey('Value {source_value} is not a valid string.')
        ->toHaveKey('Value {source_value} is not a valid integer.')
        ->toHaveKey('Value {source_value} does not match type {expected_type}.');
});

it('содержит корректные русские переводы', function (): void {
    $translations = RussianMessageFormatter::TRANSLATIONS;

    expect($translations['Cannot be empty.']['ru'])->toBe('Не может быть пустым.')
        ->and($translations['Value {source_value} is not a valid string.']['ru'])
        ->toBe('Значение {source_value} не является допустимой строкой.');
});

it('имеет метод format', function (): void {
    $reflection = new ReflectionClass(RussianMessageFormatter::class);

    expect($reflection->hasMethod('format'))->toBeTrue();

    $method = $reflection->getMethod('format');

    expect($method->getNumberOfParameters())->toBe(1);
});

it('format method is public', function (): void {
    $reflection = new ReflectionClass(RussianMessageFormatter::class);
    $method = $reflection->getMethod('format');

    expect($method->isPublic())->toBeTrue();
});

it('format method returns NodeMessage', function (): void {
    $reflection = new ReflectionClass(RussianMessageFormatter::class);
    $method = $reflection->getMethod('format');

    $returnType = $method->getReturnType();
    expect($returnType)->not->toBeNull()
        ->and($returnType->getName())->toBe('CuyZ\Valinor\Mapper\Tree\Message\NodeMessage');
});

it('format method accepts NodeMessage parameter', function (): void {
    $reflection = new ReflectionClass(RussianMessageFormatter::class);
    $method = $reflection->getMethod('format');

    $parameters = $method->getParameters();
    expect($parameters)->toHaveCount(1)
        ->and($parameters[0]->getName())->toBe('message')
        ->and($parameters[0]->getType()?->getName())->toBe('CuyZ\Valinor\Mapper\Tree\Message\NodeMessage');
});

it('TRANSLATIONS contains multiple error types', function (): void {
    $translations = RussianMessageFormatter::TRANSLATIONS;

    $errorTypes = [
        'Value {source_value} does not match any of {allowed_values}.',
        'Value {source_value} does not match any of {allowed_types}.',
        'Value {source_value} is not a valid boolean.',
        'Value {source_value} is not a valid float.',
        'Value {source_value} is not a valid negative integer.',
        'Value {source_value} is not a valid positive integer.',
    ];

    foreach ($errorTypes as $errorType) {
        expect($translations)->toHaveKey($errorType);
    }
});
