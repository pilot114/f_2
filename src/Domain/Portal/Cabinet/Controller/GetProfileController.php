<?php

declare(strict_types=1);

namespace App\Domain\Portal\Cabinet\Controller;

use App\Common\Attribute\RpcMethod;
use App\Domain\Portal\Cabinet\DTO\GetProfileResponse;
use App\Domain\Portal\Cabinet\UseCase\GetProfileUseCase;
use App\Domain\Portal\Security\Entity\SecurityUser;

class GetProfileController
{
    public function __construct(
        private GetProfileUseCase $useCase,
        private SecurityUser $currentUser,
    ) {
    }

    #[RpcMethod(
        'portal.cabinet.getProfile',
        'получение профиля пользователя',
        examples: [
            [
                'summary' => 'получение профиля пользователя',
                'params'  => [],
            ],
        ],
    )]
    public function __invoke(
    ): GetProfileResponse {
        $profile = $this->useCase->getProfile($this->currentUser->id);
        return GetProfileResponse::build($profile);
    }
}
