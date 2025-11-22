<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Finance\KPI\UseCase;

use App\Domain\Finance\Kpi\Entity\Deputy;
use App\Domain\Finance\Kpi\Entity\DeputyUser;
use App\Domain\Finance\Kpi\Repository\DeputyCommandRepository;
use App\Domain\Finance\Kpi\Repository\DeputyUserQueryRepository;
use App\Domain\Finance\Kpi\UseCase\WriteDeputyUseCase;
use Database\ORM\Attribute\Loader;
use Database\ORM\QueryRepositoryInterface;
use DateTimeImmutable;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

beforeEach(function (): void {
    $this->writeRepo = mock(DeputyCommandRepository::class);
    $this->readRepo = mock(QueryRepositoryInterface::class);
    $this->readDeputyUser = mock(DeputyUserQueryRepository::class);

    $this->useCase = new WriteDeputyUseCase(
        $this->writeRepo,
        $this->readRepo,
        $this->readDeputyUser
    );

    $this->startDate = new DateTimeImmutable('2025-01-01');
    $this->endDate = new DateTimeImmutable('2025-01-31');
    $this->deputyUserId = 123;
    $this->currentUserId = 456;
    $this->deputyId = 789;

    // Создаем макет DeputyUser
    $this->deputyUser = mock(DeputyUser::class);
    $this->deputyUser->allows('toArray')->andReturn([
        'id'             => $this->deputyUserId,
        'name'           => 'Иван Иванов',
        'positionName'   => 'Менеджер',
        'departmentName' => 'Отдел продаж',
    ]);
});

// Тесты для метода create

test('create должен успешно создавать заместителя', function (): void {
    // Arrange
    $deputy = new Deputy(
        Loader::ID_FOR_INSERT,
        $this->currentUserId,
        $this->deputyUser,
        $this->startDate,
        $this->endDate
    );

    $expectedDeputy = new Deputy(
        $this->deputyId,
        $this->currentUserId,
        $this->deputyUser,
        $this->startDate,
        $this->endDate
    );

    $this->readDeputyUser->expects('findByUserId')
        ->with($this->deputyUserId)
        ->andReturn($this->deputyUser);

    $this->writeRepo->expects('createDeputy')
        ->withArgs(function ($arg): bool {
            return $arg instanceof Deputy &&
                $arg->id === Loader::ID_FOR_INSERT;
        })
        ->andReturn($expectedDeputy);

    // Act
    $result = $this->useCase->create(
        $this->startDate,
        $this->endDate,
        $this->deputyUserId,
        $this->currentUserId
    );

    // Assert
    expect($result)->toBeInstanceOf(Deputy::class)
        ->and($result->id)->toBe($this->deputyId);
});

test('create должен выбрасывать исключение если пользователь-заместитель не найден', function (): void {
    // Arrange
    $this->readDeputyUser->expects('findByUserId')
        ->with($this->deputyUserId)
        ->andReturn(null);

    // Act & Assert
    expect(fn () => $this->useCase->create(
        $this->startDate,
        $this->endDate,
        $this->deputyUserId,
        $this->currentUserId
    ))->toThrow(
        NotFoundHttpException::class,
        "Не найден пользователь с id: {$this->deputyUserId}"
    );
});

// Тесты для метода update

test('update должен успешно обновлять данные заместителя', function (): void {
    // Arrange
    $deputy = new Deputy(
        $this->deputyId,
        $this->currentUserId,
        $this->deputyUser,
        $this->startDate,
        $this->endDate
    );

    $this->readRepo->expects('findOrFail')
        ->with($this->deputyId, 'Не найден заместитель')
        ->andReturn($deputy);

    $this->readDeputyUser->expects('findByUserId')
        ->with($this->deputyUserId)
        ->andReturn($this->deputyUser);

    $this->writeRepo->expects('updateDeputy')
        ->withArgs(function ($arg): bool {
            return $arg instanceof Deputy &&
                $arg->id === $this->deputyId;
        })
        ->andReturn($deputy);

    // Act
    $result = $this->useCase->update(
        $this->deputyId,
        $this->startDate,
        $this->endDate,
        $this->deputyUserId
    );

    // Assert
    expect($result)->toBeInstanceOf(Deputy::class)
        ->and($result->id)->toBe($this->deputyId);
});

test('update должен выбрасывать исключение если заместитель не найден', function (): void {
    // Arrange
    $this->readRepo->expects('findOrFail')
        ->with($this->deputyId, 'Не найден заместитель')
        ->andThrow(new NotFoundHttpException('Не найден заместитель'));

    // Act & Assert
    expect(fn () => $this->useCase->update(
        $this->deputyId,
        $this->startDate,
        $this->endDate,
        $this->deputyUserId
    ))->toThrow(
        NotFoundHttpException::class,
        'Не найден заместитель'
    );
});

test('update должен выбрасывать исключение если пользователь-заместитель не найден', function (): void {
    // Arrange
    $deputy = new Deputy(
        $this->deputyId,
        $this->currentUserId,
        $this->deputyUser,
        $this->startDate,
        $this->endDate
    );

    $this->readRepo->expects('findOrFail')
        ->with($this->deputyId, 'Не найден заместитель')
        ->andReturn($deputy);

    $this->readDeputyUser->expects('findByUserId')
        ->with($this->deputyUserId)
        ->andReturn(null);

    // Act & Assert
    expect(fn () => $this->useCase->update(
        $this->deputyId,
        $this->startDate,
        $this->endDate,
        $this->deputyUserId
    ))->toThrow(
        NotFoundHttpException::class,
        "Не найден пользователь с id: {$this->deputyUserId}"
    );
});

// Тесты для метода delete

test('delete должен успешно удалять заместителя', function (): void {
    // Arrange
    $this->readRepo->expects('findOrFail')
        ->with($this->deputyId, 'Не найден заместитель')
        ->andReturn(new Deputy(
            $this->deputyId,
            $this->currentUserId,
            $this->deputyUser,
            $this->startDate,
            $this->endDate
        ));

    $this->writeRepo->expects('deleteDeputy')
        ->with($this->deputyId)
        ->andReturn(true);

    // Act
    $result = $this->useCase->delete($this->deputyId);

    // Assert
    expect($result)->toBeTrue();
});

test('delete должен выбрасывать исключение если заместитель не найден', function (): void {
    // Arrange
    $this->readRepo->expects('findOrFail')
        ->with($this->deputyId, 'Не найден заместитель')
        ->andThrow(new NotFoundHttpException('Не найден заместитель'));

    // Act & Assert
    expect(fn () => $this->useCase->delete($this->deputyId))
        ->toThrow(
            NotFoundHttpException::class,
            'Не найден заместитель'
        );
});
