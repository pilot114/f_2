<?php

declare(strict_types=1);

namespace App\Domain\Portal\Cabinet\Controller;

use App\Common\Attribute\RpcMethod;
use App\Common\Attribute\RpcParam;
use App\Domain\Portal\Cabinet\DTO\ChangeAvatarResponse;
use App\Domain\Portal\Cabinet\UseCase\ChangeAvatarUseCase;
use App\Domain\Portal\Security\Entity\SecurityUser;

class ChangeAvatarController
{
    public function __construct(
        private SecurityUser $securityUser,
        private ChangeAvatarUseCase $useCase,
    ) {
    }

    #[RpcMethod(
        'portal.cabinet.changeAvatar',
        'изменение аватара пользователя',
        examples: [
            [
                'summary' => 'изменение аватара пользователя',
                'params'  => [
                    'imageBase64' => 'base64',
                ],
            ],
        ],
    )]
    public function __invoke(
        #[RpcParam('аватар пользователя в формате base64')]
        string $imageBase64,
    ): ChangeAvatarResponse {
        $avatar = $this->useCase->changeAvatar($imageBase64, $this->securityUser->id);

        return ChangeAvatarResponse::build($avatar);
    }
}
