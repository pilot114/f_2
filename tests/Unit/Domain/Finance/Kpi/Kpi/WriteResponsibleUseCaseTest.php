<?php

declare(strict_types=1);

use App\Domain\Finance\Kpi\Entity\KpiResponsible;
use App\Domain\Finance\Kpi\Entity\KpiResponsibleEnterprise;
use App\Domain\Finance\Kpi\Entity\KpiResponsibleUser;
use App\Domain\Finance\Kpi\Repository\KpiResponsibleQueryRepository;
use App\Domain\Finance\Kpi\UseCase\WriteResponsibleUseCase;
use Database\ORM\Attribute\Loader;
use Database\ORM\CommandRepositoryInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

beforeEach(function (): void {
    $this->writeRepo = Mockery::mock(CommandRepositoryInterface::class);
    $this->readRepo = Mockery::mock(KpiResponsibleQueryRepository::class);

    $this->useCase = new WriteResponsibleUseCase(
        $this->writeRepo,
        $this->readRepo
    );

    $this->enterpriseId = 1;
    $this->userId = 2;
    $this->currentUserId = 3;
    $this->responsibleId = 4;
});

test('create method creates a new responsibility record', function (): void {
    // Подготовка объекта для создания
    $responsibleToCreate = new KpiResponsible(
        id: Loader::ID_FOR_INSERT,
        user: new KpiResponsibleUser(
            id: $this->userId
        ),
        enterprise: new KpiResponsibleEnterprise(
            id: $this->enterpriseId
        ),
        changeDate: new DateTimeImmutable(),
        changeUserId: $this->currentUserId,
    );

    // Подготовка ответного объекта со сгенерированным ID
    $responsibleCreated = new KpiResponsible(
        id: $this->responsibleId,
        user: new KpiResponsibleUser(
            id: $this->userId
        ),
        enterprise: new KpiResponsibleEnterprise(
            id: $this->enterpriseId
        ),
        changeDate: new DateTimeImmutable(),
        changeUserId: $this->currentUserId,
    );

    // Ожидания к взаимодействию с репозиториями
    $this->writeRepo->shouldReceive('create')
        ->once()
        ->with(Mockery::on(function ($arg) use ($responsibleToCreate): bool {
            return $arg->id === $responsibleToCreate->id;
        }))
        ->andReturn($responsibleCreated);

    // Ожидания для получения записи с полными данными
    $responsibleWithData = new KpiResponsible(
        id: $this->responsibleId,
        user: new KpiResponsibleUser(
            id: $this->userId,
            name: 'User Name',
            responseName: 'Response Name'
        ),
        enterprise: new KpiResponsibleEnterprise(
            id: $this->enterpriseId,
            name: 'Enterprise Name'
        ),
        changeDate: new DateTimeImmutable(),
        changeUserId: $this->currentUserId,
    );

    $this->readRepo->shouldReceive('getResponsible')
        ->once()
        ->with($this->responsibleId)
        ->andReturn($responsibleWithData);

    // Выполнение
    $result = $this->useCase->create($this->enterpriseId, $this->userId, $this->currentUserId);

    // Проверка результата
    expect($result)->toBeInstanceOf(KpiResponsible::class)
        ->and($result->id)->toBe($this->responsibleId)
        ->and($result->toArray()['user']['id'])->toBe($this->userId)
        ->and($result->toArray()['user']['name'])->toBe('User Name')
        ->and($result->toArray()['user']['responseName'])->toBe('Response Name')
        ->and($result->toArray()['enterprise']['id'])->toBe($this->enterpriseId)
        ->and($result->toArray()['enterprise']['name'])->toBe('Enterprise Name');
});

test('update method updates an existing responsibility record', function (): void {
    // Данные для обновления
    $newEnterpriseId = 5;
    $newUserId = 6;

    // Подготовка существующего ответственного
    $existingResponsible = new KpiResponsible(
        id: $this->responsibleId,
        user: new KpiResponsibleUser(
            id: $this->userId
        ),
        enterprise: new KpiResponsibleEnterprise(
            id: $this->enterpriseId
        ),
        changeDate: new DateTimeImmutable(),
        changeUserId: $this->currentUserId,
    );

    // Ожидания для поиска существующей записи
    $this->readRepo->shouldReceive('findOrFail')
        ->once()
        ->with($this->responsibleId, 'Не найден ответственный за KPI')
        ->andReturn($existingResponsible);

    // Ожидания для обновления записи
    $this->writeRepo->shouldReceive('update')
        ->once()
        ->with(Mockery::on(function ($arg) use ($existingResponsible): bool {
            return $arg->id === $existingResponsible->id;
        }))
        ->andReturn($existingResponsible);

    // Ожидания для получения обновленной записи с полными данными
    $updatedResponsible = new KpiResponsible(
        id: $this->responsibleId,
        user: new KpiResponsibleUser(
            id: $newUserId,
            name: 'New User Name',
            responseName: 'New Response Name'
        ),
        enterprise: new KpiResponsibleEnterprise(
            id: $newEnterpriseId,
            name: 'New Enterprise Name'
        ),
        changeDate: new DateTimeImmutable(),
        changeUserId: $this->currentUserId,
    );

    $this->readRepo->shouldReceive('getResponsible')
        ->once()
        ->with($this->responsibleId)
        ->andReturn($updatedResponsible);

    // Выполнение
    $result = $this->useCase->update($this->responsibleId, $newEnterpriseId, $newUserId, $this->currentUserId);

    // Проверка результата
    expect($result)->toBeInstanceOf(KpiResponsible::class)
        ->and($result->id)->toBe($this->responsibleId)
        ->and($result->toArray()['user']['id'])->toBe($newUserId)
        ->and($result->toArray()['user']['name'])->toBe('New User Name')
        ->and($result->toArray()['user']['responseName'])->toBe('New Response Name')
        ->and($result->toArray()['enterprise']['id'])->toBe($newEnterpriseId)
        ->and($result->toArray()['enterprise']['name'])->toBe('New Enterprise Name');
});

test('update method throws exception for non-existent record', function (): void {
    // Ожидания для поиска несуществующей записи
    $this->readRepo->shouldReceive('findOrFail')
        ->once()
        ->with($this->responsibleId, 'Не найден ответственный за KPI')
        ->andThrow(new NotFoundHttpException("Не найден ответственный с id = {$this->responsibleId}"));

    // Проверка на генерацию исключения
    expect(fn () => $this->useCase->update($this->responsibleId, $this->enterpriseId, $this->userId, $this->currentUserId))
        ->toThrow(NotFoundHttpException::class, "Не найден ответственный с id = {$this->responsibleId}");
});

test('delete method deletes a responsibility record', function (): void {
    // Ожидания для удаления записи
    $this->writeRepo->shouldReceive('delete')
        ->once()
        ->with($this->responsibleId)
        ->andReturn(true);

    // Выполнение и проверка результата
    expect($this->useCase->delete($this->responsibleId))->toBeTrue();
});

test('delete method returns false when deletion fails', function (): void {
    // Ожидания для неудачного удаления записи
    $this->writeRepo->shouldReceive('delete')
        ->once()
        ->with($this->responsibleId)
        ->andReturn(false);

    // Выполнение и проверка результата
    expect($this->useCase->delete($this->responsibleId))->toBeFalse();
});

afterEach(function (): void {
    Mockery::close();
});
