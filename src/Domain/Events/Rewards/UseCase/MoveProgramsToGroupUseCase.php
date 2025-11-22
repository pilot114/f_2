<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\UseCase;

use App\Domain\Events\Rewards\Entity\Group;
use App\Domain\Events\Rewards\Enum\GroupType;
use App\Domain\Events\Rewards\Repository\ProgramCommandRepository;
use App\Domain\Events\Rewards\Repository\ProgramQueryRepository;
use Database\Connection\TransactionInterface;
use Database\ORM\QueryRepositoryInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MoveProgramsToGroupUseCase
{
    public function __construct(
        /** @var QueryRepositoryInterface<Group> */
        private QueryRepositoryInterface $readGroup,
        private ProgramQueryRepository $readProgram,
        private ProgramCommandRepository $writeProgram,
        private TransactionInterface $transaction,
    ) {
    }

    public function moveProgramsToGroup(int $groupId,array $programIds): void
    {
        $group = $this->readGroup->findOneBy([
            'id'         => $groupId,
            'group_type' => GroupType::GROUP->value,
        ]);
        if ($group === null) {
            throw new NotFoundHttpException("не существует группы с id = $groupId");
        }

        $programs = $this->readProgram->getByIds($programIds);
        $this->transaction->beginTransaction();
        $this->writeProgram->moveProgramsToGroup($group, $programs);
        $this->transaction->commit();
    }
}
