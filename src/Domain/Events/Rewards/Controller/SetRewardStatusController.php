<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\Controller;

use App\Common\Attribute\CpAction;
use App\Common\Attribute\RpcMethod;
use App\Common\Attribute\RpcParam;
use App\Domain\Events\Rewards\UseCase\SetRewardStatusUseCase;
use Symfony\Component\Validator\Constraints as Assert;

class SetRewardStatusController
{
    public function __construct(
        private SetRewardStatusUseCase $useCase
    ) {
    }

    #[RpcMethod(
        'events.rewards.setStatus',
        'Изменение статуса награды в стране',
        examples: [
            [
                'summary' => 'Изменение статуса награды в стране',
                'params'  => [
                    'rewardId' => '1234',
                    'active'   => [
                        1, 9,
                    ],
                    'archive' => [
                        2, 3,
                    ],
                ],
            ],
        ],
    )]
    #[CpAction('awards_directory.edit')]
    public function __invoke(
        #[RpcParam('id награды')]
        #[Assert\NotBlank]
        int   $rewardId,
        #[RpcParam('id стран в которых награда в статусе "Актуально"')]
        #[Assert\All([
            new Assert\Type('integer', 'Значение {{ value }} в списке активных стран имеет неверный тип. Ожидается {{ type }}'),
        ])]
        array $active,
        #[RpcParam('id стран в которых награда в статусе "Архив"')]
        #[Assert\All([
            new Assert\Type('integer', 'Значение {{ value }} в списке архивных стран имеет неверный тип. Ожидается {{ type }}'),
        ])]
        array $archive,
    ): void {
        $this->useCase->setRewardStatus($rewardId, $active, $archive);
    }
}
