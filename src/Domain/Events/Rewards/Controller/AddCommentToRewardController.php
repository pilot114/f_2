<?php

declare(strict_types=1);

namespace App\Domain\Events\Rewards\Controller;

use App\Common\Attribute\CpAction;
use App\Common\Attribute\RpcMethod;
use App\Common\Attribute\RpcParam;
use App\Domain\Events\Rewards\UseCase\AddCommentToRewardUseCase;
use Symfony\Component\Validator\Constraints as Assert;

class AddCommentToRewardController
{
    public function __construct(
        private AddCommentToRewardUseCase $useCase
    ) {
    }

    #[RpcMethod(
        'events.rewards.addCommentToReward',
        'Добавление комментария к награде',
        examples: [
            [
                'summary' => 'Добавление комментария к награде',
                'params'  => [
                    'rewardId' => 1234,
                    'comment'  => 'Комментарий к награде',
                ],
            ],
        ],
    )]
    #[CpAction('awards_directory.edit')]
    public function __invoke(
        #[RpcParam('Id награды')]
        #[Assert\NotBlank]
        int $rewardId,

        #[RpcParam('Комментарий к награде')]
        #[Assert\Length(
            max: 200,
            maxMessage: 'Длина параметра не может быть больше 200 символов')]
        ?string $comment = null,
    ): void {
        $this->useCase->addCommentToReward($rewardId, $comment);
    }
}
