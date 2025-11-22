<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\Controller;

use App\Common\Attribute\CpAction;
use App\Common\Attribute\RpcMethod;
use App\Common\Attribute\RpcParam;
use App\Domain\Events\Rewards\DTO\GroupResponse;
use App\Domain\Events\Rewards\UseCase\CreateGroupUseCase;
use Symfony\Component\Validator\Constraints as Assert;

class CreateGroupController
{
    public function __construct(
        private CreateGroupUseCase $useCase
    ) {
    }

    /**
     * @param array<int> $programIds
     */
    #[RpcMethod(
        'events.rewards.createGroup',
        'Создание группы программ',
        examples: [
            [
                'summary' => 'Создание группы программ',
                'params'  => [
                    'name'       => 'Новая группа программ',
                    'programIds' => [1, 2, 3],
                ],
            ],
        ],
    )]
    #[CpAction('awards_directory.edit')]
    public function __invoke(
        #[RpcParam('Название новой программы')]
        #[Assert\NotBlank]
        #[Assert\Length(
            max: 100,
            maxMessage: 'Длина параметра не может быть больше 100 символов')]
        string $name,

        #[RpcParam('Список id программ, которые будут добавлены в созданную группу')]
        #[Assert\All([
            new Assert\Type('integer', 'Значение {{ value }} имеет неверный тип. Ожидается {{ type }}'),
        ])]
        array $programIds = [],
    ): GroupResponse {
        $entity = $this->useCase->createGroup($name, $programIds);
        return GroupResponse::build($entity);
    }
}
