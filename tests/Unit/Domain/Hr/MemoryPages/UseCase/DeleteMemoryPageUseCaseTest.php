<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Hr\MemoryPages\UseCase;

use App\Domain\Hr\MemoryPages\DTO\GetMemoryPageRequest;
use App\Domain\Hr\MemoryPages\Entity\Employee;
use App\Domain\Hr\MemoryPages\Entity\MemoryPage;
use App\Domain\Hr\MemoryPages\MemoryPagePhotoService;
use App\Domain\Hr\MemoryPages\Repository\CommentCommandRepository;
use App\Domain\Hr\MemoryPages\Repository\MemoryPageCommandRepository;
use App\Domain\Hr\MemoryPages\Repository\MemoryPagePhotoQueryRepository;
use App\Domain\Hr\MemoryPages\Repository\MemoryPageQueryRepository;
use App\Domain\Hr\MemoryPages\Repository\WorkPeriodsCommandRepository;
use App\Domain\Hr\MemoryPages\UseCase\DeleteMemoryPageUseCase;
use App\Domain\Portal\Files\Entity\File;
use Database\Connection\TransactionInterface;
use DateTimeImmutable;
use Mockery;

afterEach(function (): void {
    Mockery::close();
});

it('deletes memory page with all related data', function (): void {
    // Arrange
    $memoryPageQueryRepo = Mockery::mock(MemoryPageQueryRepository::class);
    $memoryPageCommandRepo = Mockery::mock(MemoryPageCommandRepository::class);
    $photoService = Mockery::mock(MemoryPagePhotoService::class);
    $photoQueryRepo = Mockery::mock(MemoryPagePhotoQueryRepository::class);
    $commentCommandRepo = Mockery::mock(CommentCommandRepository::class);
    $workPeriodsCommandRepo = Mockery::mock(WorkPeriodsCommandRepository::class);
    $transaction = Mockery::mock(TransactionInterface::class);

    $useCase = new DeleteMemoryPageUseCase(
        $memoryPageQueryRepo,
        $memoryPageCommandRepo,
        $photoService,
        $photoQueryRepo,
        $commentCommandRepo,
        $workPeriodsCommandRepo,
        $transaction
    );

    $employee = new Employee(id: 1, name: 'Test User', response: []);
    $memoryPage = new MemoryPage(
        id: 10,
        employee: $employee,
        birthDate: new DateTimeImmutable('1990-01-01'),
        deathDate: new DateTimeImmutable('2024-12-31'),
        createDate: new DateTimeImmutable('2025-01-01'),
        obituary: 'Short',
        obituaryFull: 'Full',
        comments: [],
        workPeriods: []
    );

    $memoryPageQueryRepo->shouldReceive('getItem')
        ->with(Mockery::on(fn ($req): bool => $req instanceof GetMemoryPageRequest && $req->id === 10))
        ->once()
        ->andReturn($memoryPage);

    $photo1 = Mockery::mock(File::class);
    $photo1->shouldReceive('getId')->andReturn(1);
    $photo1->shouldReceive('getUserId')->andReturn(5);
    $photo1->shouldReceive('getCollectionName')->andReturn('memory_page_main');

    $photo2 = Mockery::mock(File::class);
    $photo2->shouldReceive('getId')->andReturn(2);
    $photo2->shouldReceive('getUserId')->andReturn(5);
    $photo2->shouldReceive('getCollectionName')->andReturn('memory_page_other');

    $photoQueryRepo->shouldReceive('getAllForMemoryPage')
        ->with($memoryPage)
        ->once()
        ->andReturn(collect([$photo1, $photo2]));

    $transaction->shouldReceive('beginTransaction')->once();
    $transaction->shouldReceive('commit')->once();

    $commentCommandRepo->shouldReceive('deleteAllComments')
        ->with(10)
        ->once();

    $workPeriodsCommandRepo->shouldReceive('deleteAllWorkPeriods')
        ->with(10)
        ->once();

    $memoryPageCommandRepo->shouldReceive('delete')
        ->with(10)
        ->once();

    $photoService->shouldReceive('commonDelete')
        ->with(1, 5)
        ->once();

    $photoService->shouldReceive('commonDelete')
        ->with(2, 5)
        ->once();

    // Act
    $useCase->deleteMemoryPage(10);

    // Assert - Mockery will verify expectations
    expect(true)->toBeTrue();
});

it('deletes memory page in transaction', function (): void {
    // Arrange
    $memoryPageQueryRepo = Mockery::mock(MemoryPageQueryRepository::class);
    $memoryPageCommandRepo = Mockery::mock(MemoryPageCommandRepository::class);
    $photoService = Mockery::mock(MemoryPagePhotoService::class);
    $photoQueryRepo = Mockery::mock(MemoryPagePhotoQueryRepository::class);
    $commentCommandRepo = Mockery::mock(CommentCommandRepository::class);
    $workPeriodsCommandRepo = Mockery::mock(WorkPeriodsCommandRepository::class);
    $transaction = Mockery::mock(TransactionInterface::class);

    $useCase = new DeleteMemoryPageUseCase(
        $memoryPageQueryRepo,
        $memoryPageCommandRepo,
        $photoService,
        $photoQueryRepo,
        $commentCommandRepo,
        $workPeriodsCommandRepo,
        $transaction
    );

    $employee = new Employee(id: 1, name: 'Test User', response: []);
    $memoryPage = new MemoryPage(
        id: 10,
        employee: $employee,
        birthDate: new DateTimeImmutable('1990-01-01'),
        deathDate: new DateTimeImmutable('2024-12-31'),
        createDate: new DateTimeImmutable('2025-01-01'),
        obituary: 'Short',
        obituaryFull: 'Full',
        comments: [],
        workPeriods: []
    );

    $memoryPageQueryRepo->shouldReceive('getItem')->andReturn($memoryPage);
    $photoQueryRepo->shouldReceive('getAllForMemoryPage')->andReturn(collect([]));

    // Verify transaction order
    $transaction->shouldReceive('beginTransaction')
        ->once()
        ->ordered();

    $commentCommandRepo->shouldReceive('deleteAllComments')
        ->once()
        ->ordered();

    $workPeriodsCommandRepo->shouldReceive('deleteAllWorkPeriods')
        ->once()
        ->ordered();

    $memoryPageCommandRepo->shouldReceive('delete')
        ->once()
        ->ordered();

    $transaction->shouldReceive('commit')
        ->once()
        ->ordered();

    // Act
    $useCase->deleteMemoryPage(10);

    // Assert
    expect(true)->toBeTrue();
});
