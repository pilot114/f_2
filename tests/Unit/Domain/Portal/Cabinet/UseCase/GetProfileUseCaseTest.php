<?php

declare(strict_types=1);

namespace App\Tests\Unit\User\Cabinet\UseCase;

use App\Domain\Portal\Cabinet\Entity\Profile;
use App\Domain\Portal\Cabinet\Repository\ProfileQueryRepository;
use App\Domain\Portal\Cabinet\UseCase\GetProfileUseCase;
use Mockery;

beforeEach(function (): void {
    $this->read = Mockery::mock(ProfileQueryRepository::class);

    $this->useCase = new GetProfileUseCase(
        $this->read,
    );
});

afterEach(function (): void {
    Mockery::close();
});

it('get profile by user id', function (Profile $profile): void {
    $userId = $profile->getUserId();

    $this->read->shouldReceive('getProfileByUserId')
        ->once()
        ->with($userId)
        ->andReturn($profile);

    $result = $this->useCase->getProfile($userId);

    expect(serialize($result))->toBe(serialize($profile));
})->with('profile');
