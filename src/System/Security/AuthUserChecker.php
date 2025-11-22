<?php

declare(strict_types=1);

namespace App\System\Security;

use App\Common\Attribute\AbstractAccessRightAttribute;
use App\Domain\Portal\Security\Entity\SecurityUser;
use App\Domain\Portal\Security\Repository\SecurityQueryRepository;
use App\System\RPC\Attribute\RpcMethodLoader;
use App\System\Security\Attribute\AbstractAccessRightAttributeLoader;
use App\System\Security\Attribute\CpActionLoader;
use App\System\Security\Attribute\CpMenuLoader;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class AuthUserChecker
{
    public function __construct(
        private SecurityQueryRepository $secRepo,
        private RpcMethodLoader         $rpcLoader,
        private CpActionLoader          $cpActionLoader,
        private CpMenuLoader            $cpMenuLoader,
        private bool                    $skipAuth,
        private Security                $security,
    ) {
    }

    public function checkCpActions(string $rpcMethodName): void
    {
        $this->checkAccessRightAttribute($rpcMethodName, $this->cpActionLoader, 'cp_action');
    }

    public function checkCpMenu(string $rpcMethodName): void
    {
        $this->checkAccessRightAttribute($rpcMethodName, $this->cpMenuLoader, 'cp_menu');
    }

    private function checkAccessRightAttribute(string $rpcMethodName, AbstractAccessRightAttributeLoader $loader, string $attributeType): void
    {
        if ($this->skipAuth) {
            return;
        }

        /** @var SecurityUser $currentUser */
        $currentUser = $this->security->getUser();

        $fqn = $this->rpcLoader->getFqnByMethodName($rpcMethodName);
        if ($fqn === null) {
            return;
        }

        $attribute = $loader->loadByFqn($fqn);
        if (!$attribute instanceof AbstractAccessRightAttribute) {
            return;
        }

        $attribute->setContext($currentUser, $this->secRepo);
        if ($attribute->check()) {
            return;
        }

        throw new AccessDeniedHttpException("Нет прав на $attributeType: {$attribute->expression}");
    }
}
