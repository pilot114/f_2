<?php

declare(strict_types=1);

namespace App\Tests\Unit\Events\Rewards\UseCase;

use App\Common\Helper\EnumerableWithTotal;
use App\Domain\Events\Rewards\Entity\Group;
use App\Domain\Events\Rewards\Entity\Program;
use App\Domain\Events\Rewards\Enum\GroupType;
use App\Domain\Events\Rewards\Repository\ProgramCommandRepository;
use App\Domain\Events\Rewards\Repository\ProgramQueryRepository;
use App\Domain\Events\Rewards\UseCase\CreateGroupUseCase;
use Database\Connection\TransactionInterface;
use Database\ORM\Attribute\Loader;
use Database\ORM\CommandRepositoryInterface;
use Database\ORM\QueryRepositoryInterface;
use Mockery;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

beforeEach(function (): void {
    $this->readGroup = Mockery::mock(QueryRepositoryInterface::class);
    $this->writeGroup = Mockery::mock(CommandRepositoryInterface::class);
    $this->readProgram = Mockery::mock(ProgramQueryRepository::class);
    $this->writeProgram = Mockery::mock(ProgramCommandRepository::class);
    $this->transaction = Mockery::mock(TransactionInterface::class);

    $this->useCase = new CreateGroupUseCase(
        $this->readGroup,
        $this->writeGroup,
        $this->readProgram,
        $this->writeProgram,
        $this->transaction,
    );
});

afterEach(function (): void {
    Mockery::close();
});

it('create group', function (): void {
    $name = 'Новая группа';
    $programIds = [1, 2];

    $programs = EnumerableWithTotal::build(
        [
            new Program(1, 'программа 1'),
            new Program(2, 'программа 2'),
        ],
        2
    );
    $group = new Group(Loader::ID_FOR_INSERT, $name);

    $this->readGroup->shouldReceive('findOneBy')
        ->with(
            [
                'name'       => $name,
                'group_type' => GroupType::GROUP->value,
            ]
        )->andReturn(null);
    $this->transaction->shouldReceive('beginTransaction');
    $groupCreated = new Group(123, 'группа 1');
    $this->writeGroup->shouldReceive('create')->once()->withArgs(
        function (Group $groupFromParam) use ($group): bool {
            return $groupFromParam->id === $group->id
                && $groupFromParam->getName() === $group->getName();
        }
    )->andReturn($groupCreated);
    $this->readProgram->shouldReceive('getByIds')->once()->with($programIds)->andReturn($programs);
    $this->writeProgram->shouldReceive('moveProgramsToGroup')->once()->with($groupCreated, $programs);
    $this->transaction->shouldReceive('commit');

    $result = $this->useCase->createGroup($name, $programIds);
    expect($result)->toBeInstanceOf(Group::class);
    expect($result->id)->toBe($groupCreated->id);
});

it('create group with not unique name', closure: function (): void {
    $name = 'Новая группа';
    $programIds = [1, 2];

    $group = new Group(Loader::ID_FOR_INSERT, $name);

    $this->readGroup->shouldReceive('findOneBy')
        ->with(
            [
                'name'       => $name,
                'group_type' => GroupType::GROUP->value,
            ]
        )->andReturn($group);

    $this->expectExceptionMessage("Группа с названием $name уже существует");
    $this->expectException(ConflictHttpException::class);

    $this->useCase->createGroup($name, $programIds);
});
