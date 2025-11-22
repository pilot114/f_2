<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Analytics\Mcp\UseCase;

use App\Domain\Analytics\Mcp\UseCase\FillDataUseCase;
use Database\Connection\WriteDatabaseInterface;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use ReflectionClass;

uses(MockeryPHPUnitIntegration::class);

beforeEach(function (): void {
    $this->conn = Mockery::mock(WriteDatabaseInterface::class);
});

afterEach(function (): void {
    Mockery::close();
});

it('возвращает false если база продуктивная', function (): void {
    $useCase = new FillDataUseCase(true, $this->conn);

    $result = $useCase->safeInsert('test.table', '[{"id": 1}]');

    expect($result)->toBeFalse();
});

it('возвращает false если JSON невалидный', function (): void {
    $useCase = new FillDataUseCase(false, $this->conn);

    $result = $useCase->safeInsert('test.table', 'invalid json');

    expect($result)->toBeFalse();
});

it('вставляет данные если база тестовая и JSON валидный', function (): void {
    $useCase = new FillDataUseCase(false, $this->conn);

    $this->conn->expects('insert')
        ->with('test.table', [
            'id'   => 1,
            'name' => 'test',
        ])
        ->once();

    $result = $useCase->safeInsert('test.table', '[{"id": 1, "name": "test"}]');

    expect($result)->toBeTrue();
});

it('имеет необходимые зависимости', function (): void {
    $reflection = new ReflectionClass(FillDataUseCase::class);
    $constructor = $reflection->getConstructor();

    expect($constructor)->not->toBeNull();

    $parameters = $constructor->getParameters();

    expect($parameters)->toHaveCount(2)
        ->and($parameters[0]->getName())->toBe('dbIsProd')
        ->and($parameters[1]->getName())->toBe('conn');
});
