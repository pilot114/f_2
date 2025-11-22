<?php

declare(strict_types=1);

use App\Domain\Finance\Kpi\Repository\PostQueryRepository;
use App\Domain\Finance\Kpi\UseCase\GetDepartmentPostsUseCase;

it('gets department posts', function (): void {
    $repository = Mockery::mock(PostQueryRepository::class);
    $useCase = new GetDepartmentPostsUseCase($repository);

    $expectedData = [
        [
            'postId'         => 1,
            'postName'       => 'Manager',
            'departmentId'   => 101,
            'departmentName' => 'Finance',
        ],
        [
            'postId'         => 2,
            'postName'       => 'Analyst',
            'departmentId'   => 102,
            'departmentName' => 'Accounting',
        ],
    ];

    $repository->shouldReceive('getPostsWithDepartments')
        ->withNoArgs()
        ->andReturn($expectedData);

    ##########################################
    $result = $useCase->getDepartmentPosts();
    ##########################################

    expect($result)->toBeArray()
        ->and($result)->toHaveCount(2)
        ->and($result[0]->postId)->toBe(1)
        ->and($result[1]->departmentName)->toBe('Accounting');
});

it('handles empty result from repository', function (): void {
    $repository = Mockery::mock(PostQueryRepository::class);
    $useCase = new GetDepartmentPostsUseCase($repository);

    $repository->shouldReceive('getPostsWithDepartments')
        ->withNoArgs()
        ->andReturn([]);

    ##########################################
    $result = $useCase->getDepartmentPosts();
    ##########################################

    expect($result)->toBeArray()
        ->and($result)->toBeEmpty();
});
