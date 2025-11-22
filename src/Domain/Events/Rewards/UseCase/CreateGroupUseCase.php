<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\UseCase;

use App\Domain\Events\Rewards\Entity\Group;
use App\Domain\Events\Rewards\Enum\GroupType;
use App\Domain\Events\Rewards\Repository\ProgramCommandRepository;
use App\Domain\Events\Rewards\Repository\ProgramQueryRepository;
use Database\Connection\TransactionInterface;
use Database\ORM\Attribute\Loader;
use Database\ORM\CommandRepositoryInterface;
use Database\ORM\QueryRepositoryInterface;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class CreateGroupUseCase
{
    public function __construct(
        /** @var QueryRepositoryInterface<Group> */
        private QueryRepositoryInterface $readGroup,
        /** @var CommandRepositoryInterface<Group> */
        private CommandRepositoryInterface $writeGroup,
        private ProgramQueryRepository $readProgram,
        private ProgramCommandRepository $writeProgram,
        private TransactionInterface $transaction,
    ) {
    }

    public function createGroup(string $name, array $programIds): Group
    {
        $group = $this->readGroup->findOneBy([
            'name'       => $name,
            'group_type' => GroupType::GROUP->value,
        ]);
        if ($group !== null) {
            throw new ConflictHttpException("Группа с названием $name уже существует");
        }

        $this->transaction->beginTransaction();
        $groupCreated = $this->writeGroup->create(new Group(Loader::ID_FOR_INSERT, $name));

        if ($programIds !== []) {
            $programs = $this->readProgram->getByIds($programIds);
            $this->writeProgram->moveProgramsToGroup($groupCreated, $programs);
        }
        $this->transaction->commit();

        return $groupCreated;
    }
}
