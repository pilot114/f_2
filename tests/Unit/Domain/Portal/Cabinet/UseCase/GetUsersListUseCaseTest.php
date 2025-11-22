<?php

declare(strict_types=1);

namespace App\Tests\Unit\User\Cabinet\UseCase;

use App\Domain\Portal\Cabinet\DTO\GetUsersListRequest;
use App\Domain\Portal\Cabinet\Entity\User;
use App\Domain\Portal\Cabinet\Repository\UserQueryRepository;
use App\Domain\Portal\Cabinet\UseCase\GetUsersListUseCase;
use Illuminate\Support\Collection;
use Mockery;

// Test datasets
dataset('user', [
    'test user' => [
        new User(5555, 'Петров Петр', 'petrov@test.com'),
    ],
]);

beforeEach(function (): void {
    $this->userQueryRepository = Mockery::mock(UserQueryRepository::class);

    $this->useCase = new GetUsersListUseCase(
        $this->userQueryRepository
    );
});

afterEach(function (): void {
    Mockery::close();
});

it('returns users list with search query', function (User $user): void {
    $request = new GetUsersListRequest(search: 'Петров');
    $users = new Collection([$user]);

    $this->userQueryRepository->shouldReceive('findActiveUsersWithEmail')
        ->once()
        ->with('Петров')
        ->andReturn($users);

    $result = $this->useCase->getList($request);

    expect($result)->toBe($users);
    expect($result->first())->toBe($user);
})->with('user');

it('returns empty collection when search is empty', function (): void {
    $request = new GetUsersListRequest(search: '');

    $result = $this->useCase->getList($request);

    expect($result->isEmpty())->toBeTrue();
    expect($result->isEmpty())->toBeTrue();
});

it('passes search term correctly to repository', function (): void {
    $searchTerm = 'Иванов Иван Иванович';
    $request = new GetUsersListRequest(search: $searchTerm);
    $users = new Collection([]);

    $this->userQueryRepository->shouldReceive('findActiveUsersWithEmail')
        ->once()
        ->with($searchTerm)
        ->andReturn($users);

    $this->useCase->getList($request);
});
