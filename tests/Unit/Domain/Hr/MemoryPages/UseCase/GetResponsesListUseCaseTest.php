<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Hr\MemoryPages\UseCase;

use App\Domain\Hr\MemoryPages\Entity\Response;
use App\Domain\Hr\MemoryPages\Repository\ResponsesQueryRepository;
use App\Domain\Hr\MemoryPages\UseCase\GetResponsesListUseCase;
use Illuminate\Support\Collection;
use Mockery;

afterEach(function (): void {
    Mockery::close();
});

it('gets list of responses', function (): void {
    // Arrange
    $repository = Mockery::mock(ResponsesQueryRepository::class);
    $useCase = new GetResponsesListUseCase($repository);

    $response1 = new Response(id: 1, name: 'IT Department');
    $response2 = new Response(id: 2, name: 'HR Department');
    $response3 = new Response(id: 3, name: 'Finance Department');

    $responses = collect([$response1, $response2, $response3]);

    $repository->shouldReceive('findAll')
        ->once()
        ->andReturn($responses);

    // Act
    $result = $useCase->getList();

    // Assert
    expect($result)->toBeInstanceOf(Collection::class)
        ->and($result)->toHaveCount(3)
        ->and($result)->toContain($response1, $response2, $response3);
});

it('returns empty collection when no responses found', function (): void {
    // Arrange
    $repository = Mockery::mock(ResponsesQueryRepository::class);
    $useCase = new GetResponsesListUseCase($repository);

    $emptyCollection = collect([]);

    $repository->shouldReceive('findAll')
        ->once()
        ->andReturn($emptyCollection);

    // Act
    $result = $useCase->getList();

    // Assert
    expect($result)->toBeEmpty();
});
