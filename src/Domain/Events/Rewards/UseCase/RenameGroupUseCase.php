<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\UseCase;

use App\Domain\Events\Rewards\Entity\Group;
use App\Domain\Events\Rewards\Enum\GroupType;
use Database\ORM\CommandRepositoryInterface;
use Database\ORM\QueryRepositoryInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RenameGroupUseCase
{
    public function __construct(
        /** @var QueryRepositoryInterface<Group> */
        private QueryRepositoryInterface $read,
        /** @var CommandRepositoryInterface<Group> */
        private CommandRepositoryInterface $write
    ) {
    }

    public function renameGroup(int $id, string $name): Group
    {
        $group = $this->read->findOneBy([
            'id'         => $id,
            'group_type' => GroupType::GROUP->value,
        ]);
        if ($group === null) {
            throw new NotFoundHttpException("Группа с id = $id не найдена", null, -32601);
        }
        $group->setName($name);

        return $this->write->update($group);
    }
}
