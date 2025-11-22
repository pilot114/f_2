<?php

declare(strict_types=1);

namespace App\System\Security\Attribute;

use App\Common\Attribute\CpAction;

class CpActionLoader extends AbstractAccessRightAttributeLoader
{
    protected function getAttributeClass(): string
    {
        return CpAction::class;
    }
}
