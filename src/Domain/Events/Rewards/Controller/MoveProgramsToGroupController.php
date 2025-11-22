<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\Controller;

use App\Common\Attribute\CpAction;
use App\Common\Attribute\RpcMethod;
use App\Common\Attribute\RpcParam;
use App\Domain\Events\Rewards\UseCase\MoveProgramsToGroupUseCase;
use Symfony\Component\Validator\Constraints as Assert;

class MoveProgramsToGroupController
{
    public function __construct(
        private MoveProgramsToGroupUseCase $useCase
    ) {

    }
    #[RpcMethod(
        'events.rewards.moveProgramsToGroup',
        'добавление программ в группу программ',
        examples: [
            [
                'summary' => 'добавление программ в группу программ',
                'params'  => [
                    'groupId'    => 1,
                    'programIds' => [
                        1, 2, 3,
                    ],
                ],
            ],
        ],
    )]
    #[CpAction('awards_directory.edit')]
    public function __invoke(
        #[RpcParam('Id группы')]
        #[Assert\NotBlank]
        int $groupId,
        #[RpcParam('Список id программ для перемещения')]
        #[Assert\NotBlank(message: 'список id программ для перемещения не должен быть пустым')]
        #[Assert\All([
            new Assert\Type('integer', 'Значение {{ value }} имеет неверный тип. Ожидается {{ type }}'),
        ])]
        array $programIds,
    ): void {
        $this->useCase->moveProgramsToGroup($groupId, $programIds);
    }
}
