<?php

declare(strict_types=1);

namespace App\Tests\Unit\Events\Rewards\UseCase;

use App\Common\Helper\EnumerableWithTotal;
use App\Domain\Events\Rewards\Entity\Group;
use App\Domain\Events\Rewards\Entity\Program;
use App\Domain\Events\Rewards\Enum\GroupType;
use App\Domain\Events\Rewards\Repository\ProgramCommandRepository;
use App\Domain\Events\Rewards\Repository\ProgramQueryRepository;
use App\Domain\Events\Rewards\UseCase\MoveProgramsToGroupUseCase;
use Database\Connection\TransactionInterface;
use Database\ORM\QueryRepositoryInterface;
use Mockery;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

beforeEach(function (): void {
    $this->readGroup = Mockery::mock(QueryRepositoryInterface::class);
    $this->readProgram = Mockery::mock(ProgramQueryRepository::class);
    $this->writeProgram = Mockery::mock(ProgramCommandRepository::class);
    $this->transaction = Mockery::mock(TransactionInterface::class);

    $this->useCase = new MoveProgramsToGroupUseCase(
        $this->readGroup,
        $this->readProgram,
        $this->writeProgram,
        $this->transaction
    );
});

afterEach(function (): void {
    Mockery::close();
});

it('move program to group', function (): void {
    $groupId = 1;
    $programIds = [1, 2];

    $programs = EnumerableWithTotal::build(
        [
            new Program(1, 'программа 1'),
            new Program(2, 'программа 2'),
        ],
        2
    );
    $group = new Group($groupId, 'группа 1');

    $this->readGroup->shouldReceive('findOneBy')
        ->with(
            [
                'id'         => $groupId,
                'group_type' => GroupType::GROUP->value,
            ]
        )->andReturn($group);

    $this->readProgram->shouldReceive('getByIds')->once()->with($programIds)->andReturn($programs);
    $this->transaction->shouldReceive('beginTransaction');
    $this->writeProgram->shouldReceive('moveProgramsToGroup')->once()->with($group, $programs);
    $this->transaction->shouldReceive('commit');

    $this->useCase->moveProgramsToGroup($groupId, $programIds);
});

it('move program to group not existing', function (): void {
    $groupId = 1;
    $programIds = [1, 2];

    $programs = EnumerableWithTotal::build(
        [
            new Program(1, 'программа 1'),
            new Program(2, 'программа 2'),
        ],
        2
    );

    $this->readGroup->shouldReceive('findOneBy')
        ->with(
            [
                'id'         => $groupId,
                'group_type' => GroupType::GROUP->value,
            ]
        )->andReturn(null);

    $this->expectExceptionMessage("не существует группы с id = $groupId");
    $this->expectException(NotFoundHttpException::class);

    $this->useCase->moveProgramsToGroup($groupId, $programIds);
});
