<?php

declare(strict_types=1);

namespace App\Tests\Unit\Events\Rewards\Repository;

use App\Domain\Events\Rewards\Entity\PartnerStatus;
use App\Domain\Events\Rewards\Enum\PartnerStatusType;
use App\Domain\Events\Rewards\Repository\PartnerStatusCommandRepository;
use Database\Connection\WriteDatabaseInterface;
use Database\ORM\Attribute\Loader;
use Mockery;

it('create partner status', function (): void {
    $connection = Mockery::mock(WriteDatabaseInterface::class);
    $repository = new PartnerStatusCommandRepository($connection, getDataMapper());

    $newPartnerStatus = new PartnerStatus(
        Loader::ID_FOR_INSERT,
        1,
        PartnerStatusType::NOT_VERIFIED,
        0,
        0,
    );

    $connection->shouldReceive('insert')
        ->once()
        ->andReturn($nextId = 1);

    $cratedPartnerId = $repository->createStatus($newPartnerStatus);

    expect($cratedPartnerId)->toBe($nextId);
});

it('update partner status', function (): void {
    $connection = Mockery::mock(WriteDatabaseInterface::class);
    $repository = new PartnerStatusCommandRepository($connection, getDataMapper());

    $newPartnerStatus = new PartnerStatus(
        $existingId = 1,
        1,
        PartnerStatusType::NOT_VERIFIED,
        0,
        0,
    );

    $connection->shouldReceive('update')
        ->once()
        ->andReturn($existingId);

    $updatedId = $repository->updateStatus($newPartnerStatus);

    expect($existingId)->toBe($updatedId);
});

it('update counts only', function (): void {
    $connection = Mockery::mock(WriteDatabaseInterface::class);
    $repository = new PartnerStatusCommandRepository($connection, getDataMapper());

    $newPartnerStatus = new PartnerStatus(
        $existingId = 1,
        1,
        PartnerStatusType::NOT_VERIFIED,
        0,
        0,
    );

    $connection->shouldReceive('update')
        ->once()
        ->andReturn($existingId);

    $updatedId = $repository->updateCountsOnly($newPartnerStatus);

    expect($existingId)->toBe($updatedId);
});
