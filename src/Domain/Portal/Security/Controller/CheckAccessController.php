<?php

declare(strict_types=1);

namespace App\Domain\Portal\Security\Controller;

use App\Common\Attribute\RpcMethod;
use App\Common\Attribute\RpcParam;
use App\Domain\Portal\Security\Entity\SecurityUser;
use App\Domain\Portal\Security\Repository\SecurityQueryRepository;

class CheckAccessController
{
    public function __construct(
        private SecurityQueryRepository $secRepo,
        private SecurityUser $currentUser,
        private bool $skipAuth,
    ) {
    }

    #[RpcMethod(
        'portal.security.getCpActionAccess',
        'Проверка прав доступа cp_action у текущего пользователя',
        examples: [
            [
                'summary' => 'проверка наличия cp_action у текущего пользователя',
                'params'  => [
                    'name' => 'accured_kpi.accured_kpi_superboss',
                ],
            ],
        ],
    )]
    public function getCpActionAccess(
        #[RpcParam('название права')]
        string $name,
    ): bool {
        if ($this->skipAuth) {
            return true;
        }
        return $this->secRepo->hasCpAction($this->currentUser->id, $name);
    }

    #[RpcMethod(
        'portal.security.getCpMenuAccess',
        'Проверка прав доступа cp_menu у текущего пользователя',
        examples: [
            [
                'summary' => 'проверка наличия cp_menu у текущего пользователя',
                'params'  => [
                    'name' => 'oit_celeb_empplan',
                ],
            ],
        ],
    )]
    public function getCpMenuAccess(
        #[RpcParam('название права')]
        string $name,
    ): bool {
        if ($this->skipAuth) {
            return true;
        }
        return $this->secRepo->hasCpMenu($this->currentUser->id, $name);
    }
}
