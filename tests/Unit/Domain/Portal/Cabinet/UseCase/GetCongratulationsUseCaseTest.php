<?php

declare(strict_types=1);

namespace App\Tests\Unit\User\Cabinet\UseCase;

use App\Domain\Portal\Cabinet\Entity\Congratulation;
use App\Domain\Portal\Cabinet\Repository\CongratulationsQueryRepository;
use App\Domain\Portal\Cabinet\UseCase\GetCongratulationsUseCase;
use DateTimeImmutable;
use Illuminate\Support\Collection;
use Mockery;

it('get congratulations by user id', function (Congratulation $congratulation): void {
    $read = Mockery::mock(CongratulationsQueryRepository::class);
    $useCase = new GetCongratulationsUseCase($read);
    $receiverId = 9999;
    $startFrom = new DateTimeImmutable('01.01.2024');
    $collection = new Collection();
    $collection->put($congratulation->getId(), $congratulation);

    $read->shouldReceive('findCongratulationsByReceiverId')
        ->once()
        ->withArgs(function ($receiverIdArg, DateTimeImmutable $startFromAgr) use ($receiverId, $startFrom): bool {
            return $receiverIdArg === $receiverId
                && $startFromAgr->format('d.m.Y') === $startFrom->format('d.m.Y');
        })
        ->andReturn($collection);

    $result = $useCase->getCongratulations($receiverId, $startFrom);

    expect(serialize($result))->toBe(serialize($collection));

})->with('congratulations');

it('get congratulations default date', function (Congratulation $congratulation): void {
    $read = Mockery::mock(CongratulationsQueryRepository::class);
    $useCase = new GetCongratulationsUseCase($read);
    $receiverId = 9999;
    $startFrom = null;

    $collection = new Collection();
    $collection->put($congratulation->getId(), $congratulation);

    $read->shouldReceive('findCongratulationsByReceiverId')
        ->once()
        ->withArgs(function ($receiverIdArg, DateTimeImmutable $startFromArg) use ($receiverId, $startFrom): bool {
            return $receiverIdArg === $receiverId;
        })
        ->andReturn($collection);

    $result = $useCase->getCongratulations($receiverId, $startFrom);

    expect(serialize($result))->toBe(serialize($collection));

})->with('congratulations');
