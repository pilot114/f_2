<?php

declare(strict_types=1);

namespace App\Tests\Unit\Events\Rewards\Repository;

use App\Domain\Events\Rewards\Entity\Group;
use App\Domain\Events\Rewards\Entity\Program;
use App\Domain\Events\Rewards\Repository\ProgramCommandRepository;
use Database\Connection\ParamType;
use Database\Connection\WriteDatabaseInterface;
use DateTimeInterface;
use Mockery;

it('move programs to group', function (): void {

    $connection = Mockery::mock(WriteDatabaseInterface::class);
    $repository = new ProgramCommandRepository($connection, getDataMapper());

    $group = new Group(1, 'тестовая группа');
    $programs = collect(
        [
            new Program(1, 'тестовая программа 1'),
            new Program(2, 'тестовая программа 2'),
        ]
    );

    $connection->shouldReceive('command')
        ->once()
        ->withArgs(function ($sql, array $params, array $type) use ($programs): bool {
            return $params['prog_id_list'] === $programs->map(fn (Program $program): int => $program->id)->values()->all()
                && $type['prog_id_list'] === ParamType::ARRAY_INTEGER;
        })
        ->ordered('remove');

    $programs->each(function (Program $program) use ($connection, $group): void {
        $connection->shouldReceive('insert')
            ->once()
            ->withArgs(function ($sql, array $params, array $types) use ($group, $program): bool {
                return $params['pd_group_id'] === $group->id
                    && $params['pd_prog_id'] === $program->id
                    && $params['dt'] instanceof DateTimeInterface
                    && $types['dt'] === ParamType::DATE;
            })
            ->ordered('add');
    });

    $repository->moveProgramsToGroup($group, $programs);
});
