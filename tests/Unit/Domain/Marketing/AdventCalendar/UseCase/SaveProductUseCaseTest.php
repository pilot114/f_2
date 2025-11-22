<?php

declare(strict_types=1);

namespace App\Tests\Unit\Marketing\AdventCalendar\UseCase;

use App\Domain\Marketing\AdventCalendar\Repository\WriteProductCommandRepository;
use App\Domain\Marketing\AdventCalendar\UseCase\SaveProductUseCase;
use Database\Connection\TransactionInterface;
use DomainException;
use Exception;

beforeEach(function (): void {
    $this->repository = $this->createMock(WriteProductCommandRepository::class);
    $this->transaction = $this->createMock(TransactionInterface::class);
    $this->useCase = new SaveProductUseCase($this->repository, $this->transaction);
});

test('saveProduct adds products successfully', function (): void {
    // Arrange
    $calendarId = 123;
    $codes = ['PROD001', 'PROD002', 'PROD003'];

    $this->transaction->expects($this->once())->method('beginTransaction');
    $this->repository
        ->expects($this->exactly(3))
        ->method('addProductOfMonth');

    $this->transaction->expects($this->once())->method('commit');

    // Act
    $result = $this->useCase->saveProduct($calendarId, $codes);

    // Assert
    expect($result)->toBeTrue();
});

test('saveProduct throws DomainException on repository error', function (): void {
    // Arrange
    $calendarId = 123;
    $codes = ['INVALID_PROD'];

    $this->transaction->expects($this->once())->method('beginTransaction');
    $this->repository
        ->expects($this->once())
        ->method('addProductOfMonth')
        ->willThrowException(new Exception('Database error'));

    // Act & Assert
    expect(fn () => $this->useCase->saveProduct($calendarId, $codes))
        ->toThrow(DomainException::class, 'Календарь или продукт не существует');
});

test('removeProduct deletes products successfully', function (): void {
    // Arrange
    $productIds = [1, 2, 3];

    $this->transaction->expects($this->once())->method('beginTransaction');
    $this->repository
        ->expects($this->exactly(3))
        ->method('deleteProductOfMonth');

    $this->transaction->expects($this->once())->method('commit');

    // Act
    $result = $this->useCase->removeProduct($productIds);

    // Assert
    expect($result)->toBeTrue();
});
