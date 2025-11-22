<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Hr\MemoryPages\UseCase;

use App\Domain\Hr\MemoryPages\DTO\GetMemoryPageListRequest;
use App\Domain\Hr\MemoryPages\Entity\Employee;
use App\Domain\Hr\MemoryPages\Entity\MemoryPageListItem;
use App\Domain\Hr\MemoryPages\Entity\Response;
use App\Domain\Hr\MemoryPages\MemoryPagePhotoService;
use App\Domain\Hr\MemoryPages\Repository\MemoryPageListQueryRepository;
use App\Domain\Hr\MemoryPages\UseCase\GetMemoryPageListUseCase;
use App\Domain\Portal\Files\Entity\File;
use Illuminate\Support\Collection;
use Mockery;

afterEach(function (): void {
    Mockery::close();
});

it('gets list of memory pages with main photos', function (): void {
    // Arrange
    $repository = Mockery::mock(MemoryPageListQueryRepository::class);
    $photoService = Mockery::mock(MemoryPagePhotoService::class);

    $useCase = new GetMemoryPageListUseCase($repository, $photoService);

    $employee1 = new Employee(id: 1, name: 'User 1', response: []);
    $employee2 = new Employee(id: 2, name: 'User 2', response: []);
    $response = new Response(id: 1, name: 'Department');

    $item1 = new MemoryPageListItem(
        id: 1,
        employee: $employee1,
        mainPhotoId: 10,
        obituary: 'Obituary 1',
        response: $response,
        commentsCount: 5
    );

    $item2 = new MemoryPageListItem(
        id: 2,
        employee: $employee2,
        mainPhotoId: 20,
        obituary: 'Obituary 2',
        response: $response,
        commentsCount: 3
    );

    $request = new GetMemoryPageListRequest();
    $memoryPages = collect([$item1, $item2]);

    $repository->shouldReceive('getList')
        ->with($request)
        ->once()
        ->andReturn($memoryPages);

    $photo1 = Mockery::mock(File::class);
    $photo2 = Mockery::mock(File::class);

    $photoService->shouldReceive('getById')
        ->with(10)
        ->once()
        ->andReturn($photo1);

    $photoService->shouldReceive('getById')
        ->with(20)
        ->once()
        ->andReturn($photo2);

    // Act
    $result = $useCase->getList($request);

    // Assert
    expect($result)->toBeInstanceOf(Collection::class)
        ->and($result)->toHaveCount(2)
        ->and($item1->getMainPhoto())->toBe($photo1)
        ->and($item2->getMainPhoto())->toBe($photo2);
});

it('handles empty list', function (): void {
    // Arrange
    $repository = Mockery::mock(MemoryPageListQueryRepository::class);
    $photoService = Mockery::mock(MemoryPagePhotoService::class);

    $useCase = new GetMemoryPageListUseCase($repository, $photoService);

    $request = new GetMemoryPageListRequest();
    $emptyCollection = collect([]);

    $repository->shouldReceive('getList')
        ->with($request)
        ->once()
        ->andReturn($emptyCollection);

    // Act
    $result = $useCase->getList($request);

    // Assert
    expect($result)->toBeEmpty();
});

it('loads photos for all items in list', function (): void {
    // Arrange
    $repository = Mockery::mock(MemoryPageListQueryRepository::class);
    $photoService = Mockery::mock(MemoryPagePhotoService::class);

    $useCase = new GetMemoryPageListUseCase($repository, $photoService);

    $employee = new Employee(id: 1, name: 'User', response: []);
    $response = new Response(id: 1, name: 'Dept');

    $items = [];
    for ($i = 1; $i <= 5; $i++) {
        $items[] = new MemoryPageListItem(
            id: $i,
            employee: $employee,
            mainPhotoId: $i * 10,
            obituary: "Obituary $i",
            response: $response
        );
    }

    $request = new GetMemoryPageListRequest();
    $repository->shouldReceive('getList')->andReturn(collect($items));

    // Should load photo for each item
    for ($i = 1; $i <= 5; $i++) {
        $photo = Mockery::mock(File::class);
        $photoService->shouldReceive('getById')
            ->with($i * 10)
            ->once()
            ->andReturn($photo);
    }

    // Act
    $result = $useCase->getList($request);

    // Assert
    expect($result)->toHaveCount(5);
    foreach ($items as $item) {
        expect($item->getMainPhoto())->not->toBeNull();
    }
});
