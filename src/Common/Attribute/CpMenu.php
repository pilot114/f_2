<?php

declare(strict_types=1);

namespace App\Common\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class CpMenu extends AbstractAccessRightAttribute
{
    public function check(): bool
    {
        return $this->hasPermission($this->expression);
    }

    protected function hasPermission(string $name): bool
    {
        return $this->secRepo->hasCpMenu($this->currentUser->id, $name);
    }
}
