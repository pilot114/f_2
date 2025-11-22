<?php

declare(strict_types=1);

namespace App\Domain\Portal\Cabinet\Controller;

use App\Common\Attribute\RpcMethod;
use App\Common\Attribute\RpcParam;
use App\Domain\Portal\Cabinet\DTO\GetProfileResponse;
use App\Domain\Portal\Cabinet\DTO\WorkTime;
use App\Domain\Portal\Cabinet\UseCase\ChangeProfileInfoUseCase;
use App\Domain\Portal\Security\Entity\SecurityUser;

class ChangeProfileInfoController
{
    public function __construct(
        private ChangeProfileInfoUseCase $useCase,
        private SecurityUser $currentUser,
    ) {
    }

    #[RpcMethod(
        'portal.cabinet.changeProfileInfo',
        'изменение информации профиля',
        examples: [
            [
                'summary' => 'изменение информации профиля',
                'params'  => [
                    'hideBirthday' => true,
                    'telegram'     => 'telegram',
                    'phone'        => '+7(999)999-99-99',
                    'city'         => 'Новосибирск',
                    'workTime'     => [
                        'start'    => '2025-01-01T08:00:00+00:00',
                        'end'      => '2025-01-01T18:00:00+00:00',
                        'timeZone' => 'Asia/Novosibirsk',
                    ],
                ],
            ],
        ],
    )]
    public function __invoke(
        #[RpcParam('Cкрыть др на портале')]
        ?bool $hideBirthday = null,
        #[RpcParam('Скайп')]
        ?string            $telegram = null,
        #[RpcParam('Телефон')]
        ?string            $phone = null,
        #[RpcParam('Город')]
        ?string            $city = null,
        #[RpcParam('Рабочее время')]
        ?WorkTime $workTime = null
    ): GetProfileResponse {
        $profile = $this->useCase->changeProfileInfo($this->currentUser->id, $hideBirthday, $telegram, $phone, $city, $workTime);

        return GetProfileResponse::build($profile);
    }
}
