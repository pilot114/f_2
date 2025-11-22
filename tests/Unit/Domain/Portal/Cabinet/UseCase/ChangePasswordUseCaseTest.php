<?php

declare(strict_types=1);

namespace App\Tests\Unit\User\Cabinet\UseCase;

use App\Domain\Portal\Cabinet\Entity\Password;
use App\Domain\Portal\Cabinet\Repository\PasswordCommandRepository;
use App\Domain\Portal\Cabinet\UseCase\ChangePasswordUseCase;
use Database\Connection\TransactionInterface;
use Database\ORM\QueryRepository;
use Mockery;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

beforeEach(function (): void {
    $this->read = Mockery::mock(QueryRepository::class);
    $this->write = Mockery::mock(PasswordCommandRepository::class);
    $this->transaction = Mockery::mock(TransactionInterface::class);

    $this->useCase = new ChangePasswordUseCase(
        $this->read,
        $this->write,
        $this->transaction,
    );
});

afterEach(function (): void {
    Mockery::close();
});

it('change password valid', function (): void {
    [$userId, $old, $new] = [9999, '12345', 'asd1234{}dDDd'];

    $password = new Password($userId, $old);

    $this->read->shouldReceive('findOneBy')
        ->once()
        ->with([
            'id' => $userId,
        ])
        ->andReturn($password);

    $this->transaction->shouldReceive('beginTransaction')->once();
    $this->write->shouldReceive('changePassword')
        ->once()
        ->with($password);
    $this->write->shouldReceive('markPasswordRecentlyChanged')
        ->once()
        ->with($password);
    $this->transaction->shouldReceive('commit')->once();

    $this->useCase->changePassword($userId, $old, $new);
});

it('change password not found', function (): void {
    [$userId, $old, $new] = [9999, '12345', 'asd1234{}dDDd'];

    $this->read->shouldReceive('findOneBy')
        ->once()
        ->with([
            'id' => $userId,
        ])
        ->andReturn(null);

    $this->expectException(NotFoundHttpException::class);

    $this->useCase->changePassword($userId, $old, $new);
});
