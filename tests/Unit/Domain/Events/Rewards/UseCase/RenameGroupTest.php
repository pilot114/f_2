<?php

declare(strict_types=1);

namespace App\Tests\Unit\Events\Rewards\UseCase;

use App\Domain\Events\Rewards\Entity\Group;
use App\Domain\Events\Rewards\Enum\GroupType;
use App\Domain\Events\Rewards\UseCase\RenameGroupUseCase;
use Database\ORM\CommandRepository;
use Database\ORM\QueryRepositoryInterface;
use Mockery;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

beforeEach(function (): void {
    $this->readGroup = Mockery::mock(QueryRepositoryInterface::class);
    $this->writeGroup = Mockery::mock(CommandRepository::class);

    $this->useCase = new RenameGroupUseCase(
        $this->readGroup,
        $this->writeGroup
    );
});

afterEach(function (): void {
    Mockery::close();
});

it('rename group', function (): void {
    $groupId = 1;
    $name = "Новое имя группы";

    $group = new Group($groupId, 'Старое имя группы');

    $this->readGroup->shouldReceive('findOneBy')
        ->once()
        ->with(
            [
                'id'         => $groupId,
                'group_type' => GroupType::GROUP->value,
            ],
        )
        ->andReturn($group);
    $this->writeGroup->shouldReceive('update')->once()->with($group)->andReturn($group);

    $group = $this->useCase->renameGroup($groupId, $name);
    expect($group->getName())->toBe($name);
});

it('rename group not exists', function (): void {
    $groupId = 1;
    $name = "Новое имя группы";

    $this->readGroup->shouldReceive('findOneBy')
        ->once()
        ->with(
            [
                'id'         => $groupId,
                'group_type' => GroupType::GROUP->value,
            ],
        )
        ->andReturn(null);

    $this->expectExceptionMessage("Группа с id = $groupId не найдена");
    $this->expectException(NotFoundHttpException::class);

    $this->useCase->renameGroup($groupId, $name);
});
