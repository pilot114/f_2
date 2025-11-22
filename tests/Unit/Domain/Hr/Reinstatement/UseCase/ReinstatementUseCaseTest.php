<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Hr\Reinstatement\UseCase;

use App\Domain\Hr\Reinstatement\Entity\Employee;
use App\Domain\Hr\Reinstatement\Repository\ReinstatementCommandRepository;
use App\Domain\Hr\Reinstatement\Repository\ReinstatementQueryRepository;
use App\Domain\Hr\Reinstatement\UseCase\ReinstatementUseCase;
use Database\Connection\CpConnection;
use DateTimeImmutable;
use Illuminate\Support\Collection;
use Mockery;

beforeEach(function (): void {
    $this->cRepo = Mockery::mock(ReinstatementCommandRepository::class);
    $this->qRepo = Mockery::mock(ReinstatementQueryRepository::class);
    $this->user = createSecurityUser(
        id: 42,
        name: 'test',
        email: 'test',
    );
    $this->connection = Mockery::mock(CpConnection::class);
    $this->case = new ReinstatementUseCase($this->qRepo, $this->cRepo, $this->user, $this->connection);
});

it('reinstate employee successfully', function (): void {
    $this->cRepo->shouldReceive('reinstateEmployee')->once()->andReturn(true);
    $this->connection->shouldReceive('query')->andReturn((function () {yield []; })());
    $data = $this->case->reinstateEmployee(42);
    expect($data)->toBe(true);
});

it('reinstate employee successfully email', function (): void {
    $this->cRepo->shouldReceive('reinstateEmployee')->once()->andReturn(true);
    $this->connection->shouldReceive('query')->andReturn((function () {
        yield [
            'id'          => 1,
            'name'        => 'test',
            'email'       => 'test',
            'login'       => 'test',
            'pw'          => 'test',
            'date_update' => '2025-10-16 15:03:22',

        ];
    })());
    $this->connection->shouldReceive('insert')->andReturn(1);
    $data = $this->case->reinstateEmployee(42);
    expect($data)->toBe(true);
});

it('reinstate employee unsuccesfully', function (): void {
    $this->cRepo->shouldReceive('reinstateEmployee')->once()->andReturn(false);
    $this->connection->shouldReceive('query')->andReturn((function () {yield []; })());
    $data = $this->case->reinstateEmployee(42);
    expect($data)->toBe(false);
});

it('get employees successfully', function (): void {
    $emp = new Employee(
        id: 1,
        name: 'Test Employee',
        department: 'test_department',
        quitDate: new DateTimeImmutable(),
        email: 'test_email',
        login: 'test_login'
    );

    $this->qRepo->shouldReceive('getByNamePart')->once()->andReturn(new Collection($emp));
    $data = $this->case->getEmployeeByNamePart('42');
    expect($data->get('id'))->toBe(1);
});

it('get employees empty', function (): void {

    $this->qRepo->shouldReceive('getByNamePart')->once()->andReturn(new Collection([]));
    $data = $this->case->getEmployeeByNamePart('42');
    expect($data)->toBeEmpty();
});
