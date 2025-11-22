<?php

declare(strict_types=1);

namespace App\Common\Attribute;

use App\Domain\Portal\Security\Entity\SecurityUser;
use App\Domain\Portal\Security\Repository\SecurityQueryRepository;

abstract class AbstractAccessRightAttribute
{
    protected SecurityUser $currentUser;
    protected SecurityQueryRepository $secRepo;

    public function __construct(
        public string $expression,
    ) {
    }

    public function setContext(SecurityUser $currentUser, SecurityQueryRepository $secRepo): void
    {
        $this->currentUser = $currentUser;
        $this->secRepo = $secRepo;
    }

    abstract public function check(): bool;

    abstract protected function hasPermission(string $name): bool;
}
