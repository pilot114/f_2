<?php

declare(strict_types=1);

namespace App\Tests\Unit\User\Cabinet\Repository;

use App\Domain\Portal\Cabinet\Entity\Password;
use App\Domain\Portal\Cabinet\Repository\PasswordCommandRepository;
use Database\Connection\ParamType;
use Database\Connection\WriteDatabaseInterface;
use DateTimeInterface;
use Mockery;

it('change user password', function (): void {
    $connection = Mockery::mock(WriteDatabaseInterface::class);
    $repository = new PasswordCommandRepository($connection, getDataMapper());

    $password = new Password(
        123,
        'password',
    );

    $connection->shouldReceive('procedure')
        ->once()
        ->withArgs(function (string $table, array $params) use ($password): bool {
            return $params['i_id'] === $password->getUserId()
            && $params['i_password'] === $password->getPassword();
        });

    $repository->changePassword($password);
});

it('mark password recently changed', function (): void {
    $connection = Mockery::mock(WriteDatabaseInterface::class);
    $repository = new PasswordCommandRepository($connection, getDataMapper());

    $password = new Password(
        123,
        'password',
    );

    $connection->shouldReceive('update')
        ->once()
        ->withArgs(function (string $table, array $params, array $condition, array $types) use ($password): bool {
            return $params['is_need_change_pass'] === 0
                && $params['dt_last_pass_change'] instanceof DateTimeInterface
                && $condition['id'] === $password->getUserId()
                && $types['dt_last_pass_change'] === ParamType::DATE;
        });

    $repository->markPasswordRecentlyChanged($password);
});
