<?php

declare(strict_types=1);

namespace App\Tests\Unit\System\RPC\Spec\Repository;

use App\System\RPC\Spec\Repository\MockSpecRepository;
use Database\Connection\CpConnection;
use InvalidArgumentException;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PSX\OpenRPC\OpenRPC;
use stdClass;

uses(MockeryPHPUnitIntegration::class);

beforeEach(function (): void {
    /** @var MockInterface|CpConnection $this */
    $this->connection = Mockery::mock(CpConnection::class);
    $this->repository = new MockSpecRepository($this->connection);
});

it('retrieves mock spec from database', function (): void {
    $mockSpec = '{"methods":[{"name":"test.method"}]}';

    // Создаем генератор, который будет возвращать результат запроса
    $queryResult = function () use ($mockSpec) {
        yield [
            'specification' => $mockSpec,
        ];
    };

    $this->connection->expects('query')
        ->with(
            'select cbs.SPECIFICATON as specification from test.cp_back_specs cbs where id = :id',
            [
                'id' => MockSpecRepository::MOCK_SPEC_ID,
            ]
        )
        ->andReturns($queryResult());

    expect($this->repository->getMockSpec())->toBe($mockSpec);
});

it('returns empty string when no spec is found', function (): void {
    // Пустой генератор для случая, когда результат не найден
    $queryResult = function () {
        if (false) {
            yield; // Никогда не выполняется, но определяет тип
        }
    };

    $this->connection->expects('query')
        ->andReturns($queryResult());

    expect($this->repository->getMockSpec())->toBe('');
});

it('returns empty string when spec is not a string', function (): void {
    // Генератор с неверным типом значения
    $queryResult = function () {
        yield [
            'specification' => 123,
        ];
    };

    $this->connection->expects('query')
        ->andReturns($queryResult());

    expect($this->repository->getMockSpec())->toBe('');
});

it('saves mock spec to database', function (): void {
    $openRPC = new OpenRPC();
    $openRPC->setOpenrpc('1.0.0');

    $specJson = json_encode($openRPC);

    $this->connection->expects('update')
        ->with(
            MockSpecRepository::MOCK_SPEC_TABLE,
            [
                'specificaton' => $specJson,
            ],
            [
                'id' => MockSpecRepository::MOCK_SPEC_ID,
            ]
        )
        ->andReturns(1);

    expect($this->repository->saveMockSpec($openRPC))->toBeTrue();
});

it('returns false when update affects no rows', function (): void {
    $openRPC = new OpenRPC();
    $openRPC->setOpenrpc('1.0.0');

    $this->connection->expects('update')
        ->andReturns(0);

    expect($this->repository->saveMockSpec($openRPC))->toBeFalse();
});

it('throws exception when json_encode fails', function (): void {
    // Создаем мок OpenRPC, который вернет объект с проблемным содержимым
    $problematicObject = new stdClass();
    $problematicObject->invalid = fopen('php://memory', 'r'); // Ресурсы не могут быть сериализованы

    $openRPC = Mockery::mock(OpenRPC::class);
    $openRPC->allows('jsonSerialize')
        ->andReturns($problematicObject); // Возвращаем объект вместо массива

    expect(fn () => $this->repository->saveMockSpec($openRPC))
        ->toThrow(InvalidArgumentException::class, 'Не удалось получить спецификацию мока');
});
