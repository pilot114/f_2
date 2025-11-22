<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\Controller;

use App\Common\Attribute\CpAction;
use App\Common\Attribute\RpcMethod;
use App\Common\Attribute\RpcParam;
use App\Domain\Events\Rewards\DTO\GroupResponse;
use App\Domain\Events\Rewards\UseCase\RenameGroupUseCase;
use Symfony\Component\Validator\Constraints as Assert;

class RenameGroupController
{
    public function __construct(
        private RenameGroupUseCase $useCase
    ) {
    }

    #[RpcMethod(
        'events.rewards.renameGroup',
        'Переименование группы программ',
        examples: [
            [
                'summary' => 'Переименование группы программ',
                'params'  => [
                    'id'   => 1,
                    'name' => 'Название группы программ',
                ],
            ],
        ],
    )]
    #[CpAction('awards_directory.edit')]
    public function __invoke(
        #[RpcParam('Id группы')]
        #[Assert\NotBlank]
        int $id,

        #[RpcParam('Название группы')]
        #[Assert\NotBlank(message: 'параметр name не может быть пустым')]
        #[Assert\Length(
            max: 100,
            maxMessage: 'Длина параметра не может быть больше 100 символов'
        )]
        string $name,
    ): GroupResponse {
        $entity = $this->useCase->renameGroup($id, $name);
        return GroupResponse::build($entity);
    }
}
