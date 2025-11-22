<?php

declare(strict_types=1);

use App\Domain\Marketing\CustomerHistory\DTO\EditCustomerHistoryRequest;
use App\Domain\Marketing\CustomerHistory\Enum\Status;
use App\Domain\Marketing\CustomerHistory\Repository\CustomerHistoryCommandRepository;
use App\Domain\Marketing\CustomerHistory\UseCase\EditCustomerHistoryUseCase;

beforeEach(function (): void {
    $this->repository = $this->createMock(CustomerHistoryCommandRepository::class);

    $this->securityUser = createSecurityUser(
        id: 123,
    );

    $this->useCase = new EditCustomerHistoryUseCase(
        $this->repository,
        $this->securityUser
    );
});

it('should successfully edit customer history', function (): void {
    // Arrange
    $request = new EditCustomerHistoryRequest(
        id: 1,
        status: Status::PUBLISHED,
        shops: ['ru', 'kz'],
        preview: 'Test preview',
        text: 'Test text content',
        commentary: 'Test commentary'
    );

    $this->repository
        ->expects($this->once())
        ->method('editStoryOfCustomer')
        ->with(
            id: 1,
            userId: 123,
            status: 2, // Status::PUBLISHED->value
            preview: 'Test preview',
            text: 'Test text content',
            commentary: 'Test commentary',
            shops: 'ru,kz'
        );

    // Act
    $result = $this->useCase->editCustomerHistory($request);

    // Assert
    expect($result)->toBeTrue();
});

it('should successfully edit customer history with moderation status', function (): void {
    // Arrange
    $request = new EditCustomerHistoryRequest(
        id: 42,
        status: Status::MODERATION,
        shops: ['by', 'ua', 'am'],
        preview: 'Moderation preview',
        text: 'Content under moderation',
        commentary: null
    );

    $this->repository
        ->expects($this->once())
        ->method('editStoryOfCustomer')
        ->with(
            id: 42,
            userId: 123,
            status: 1, // Status::MODERATION->value
            preview: 'Moderation preview',
            text: 'Content under moderation',
            commentary: null,
            shops: 'by,ua,am'
        );

    // Act
    $result = $this->useCase->editCustomerHistory($request);

    // Assert
    expect($result)->toBeTrue();
});

it('should successfully edit customer history with refused status', function (): void {
    // Arrange
    $request = new EditCustomerHistoryRequest(
        id: 999,
        status: Status::REFUSED,
        shops: ['ge'],
        preview: 'Refused story preview',
        text: 'This story was refused',
        commentary: 'Reason for refusal'
    );

    $this->repository
        ->expects($this->once())
        ->method('editStoryOfCustomer')
        ->with(
            id: 999,
            userId: 123,
            status: 3, // Status::REFUSED->value
            preview: 'Refused story preview',
            text: 'This story was refused',
            commentary: 'Reason for refusal',
            shops: 'ge'
        );

    // Act
    $result = $this->useCase->editCustomerHistory($request);

    // Assert
    expect($result)->toBeTrue();
});

it('should throw domain exception when repository throws exception', function (): void {
    // Arrange
    $request = new EditCustomerHistoryRequest(
        id: 1,
        status: Status::PUBLISHED,
        shops: ['ru'],
        preview: 'Test preview',
        text: 'Test text',
        commentary: 'Test commentary'
    );

    $originalException = new Exception('Database connection failed');

    $this->repository
        ->expects($this->once())
        ->method('editStoryOfCustomer')
        ->willThrowException($originalException);

    // Act & Assert
    expect(fn () => $this->useCase->editCustomerHistory($request))
        ->toThrow(DomainException::class, 'Ошибка сохранения истории клиента: Database connection failed');
});

it('should preserve original exception in domain exception', function (): void {
    // Arrange
    $request = new EditCustomerHistoryRequest(
        id: 1,
        status: Status::MODERATION,
        shops: ['kz'],
        preview: 'Test preview',
        text: 'Test text',
        commentary: ''
    );

    $originalException = new RuntimeException('Connection timeout');

    $this->repository
        ->method('editStoryOfCustomer')
        ->willThrowException($originalException);

    // Act & Assert
    try {
        $this->useCase->editCustomerHistory($request);
        $this->fail('Expected DomainException to be thrown');
    } catch (DomainException $e) {
        expect($e->getCode())->toBe(400);
        expect($e->getPrevious())->toBe($originalException);
        expect($e->getMessage())->toBe('Ошибка сохранения истории клиента: Connection timeout');
    }
});

it('should handle multiple shops correctly', function (): void {
    // Arrange
    $request = new EditCustomerHistoryRequest(
        id: 5,
        status: Status::PUBLISHED,
        shops: ['ru', 'kz', 'by', 'ua', 'am', 'ge'],
        preview: 'Multi-shop story',
        text: 'Story for multiple countries',
        commentary: 'Published in all shops'
    );

    $this->repository
        ->expects($this->once())
        ->method('editStoryOfCustomer')
        ->with(
            id: 5,
            userId: 123,
            status: 2,
            preview: 'Multi-shop story',
            text: 'Story for multiple countries',
            commentary: 'Published in all shops',
            shops: 'ru,kz,by,ua,am,ge' // Проверяем корректную склейку
        );

    // Act
    $result = $this->useCase->editCustomerHistory($request);

    // Assert
    expect($result)->toBeTrue();
});

it('should use different security user id correctly', function (): void {
    // Arrange
    $customSecurityUser = createSecurityUser(
        id: 456,
        name: 'Custom User',
        email: 'custom@example.com',
    );

    $customUseCase = new EditCustomerHistoryUseCase(
        $this->repository,
        $customSecurityUser
    );

    $request = new EditCustomerHistoryRequest(
        id: 10,
        status: Status::MODERATION,
        shops: ['ru'],
        preview: 'Test with custom user',
        text: 'Content by custom user',
        commentary: 'Custom user comment'
    );

    $this->repository
        ->expects($this->once())
        ->method('editStoryOfCustomer')
        ->with(
            id: 10,
            userId: 456, // Проверяем использование корректного user ID
            status: 1,
            preview: 'Test with custom user',
            text: 'Content by custom user',
            commentary: 'Custom user comment',
            shops: 'ru'
        );

    // Act
    $result = $customUseCase->editCustomerHistory($request);

    // Assert
    expect($result)->toBeTrue();
});

it('should handle empty commentary correctly', function (): void {
    // Arrange
    $request = new EditCustomerHistoryRequest(
        id: 7,
        status: Status::PUBLISHED,
        shops: ['kz'],
        preview: 'Story without commentary',
        text: 'This story has no commentary',
        commentary: '' // Пустой комментарий
    );

    $this->repository
        ->expects($this->once())
        ->method('editStoryOfCustomer')
        ->with(
            id: 7,
            userId: 123,
            status: 2,
            preview: 'Story without commentary',
            text: 'This story has no commentary',
            commentary: '',
            shops: 'kz'
        );

    // Act
    $result = $this->useCase->editCustomerHistory($request);

    // Assert
    expect($result)->toBeTrue();
});
