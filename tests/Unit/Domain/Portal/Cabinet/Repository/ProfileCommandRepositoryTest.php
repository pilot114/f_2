<?php

declare(strict_types=1);

namespace App\Tests\Unit\User\Cabinet\Repository;

use App\Domain\Portal\Cabinet\Entity\Profile;
use App\Domain\Portal\Cabinet\Repository\ProfileCommandRepository;
use Database\Connection\WriteDatabaseInterface;
use Mockery;

it('change user password', function (Profile $profile): void {
    $connection = Mockery::mock(WriteDatabaseInterface::class);
    $repository = new ProfileCommandRepository($connection, getDataMapper());

    $connection->shouldReceive('update')
        ->once()
        ->withArgs(function (string $table, array $params, array $condition) use ($profile): bool {
            return $params['telegram'] === $profile->getTelegram()
                && $params['office_phone_city'] === $profile->getPhone()
                && $params['work_address'] === $profile->getCity()
                && $condition['id'] === $profile->getUserId();
        })
        ->ordered(1);

    $connection->shouldReceive('update')
        ->once()
        ->withArgs(function (string $table, array $params, array $condition) use ($profile): bool {
            return $params['hide_birthday'] === (int) $profile->getHideBirthday()
                && $condition['idemp'] === $profile->getUserId();
        })
        ->ordered(2);

    $repository->updateInfo($profile);
})->with('profile');
