<?php

declare(strict_types=1);

namespace App\System\Security\Attribute;

use App\Common\Attribute\CpMenu;

class CpMenuLoader extends AbstractAccessRightAttributeLoader
{
    protected function getAttributeClass(): string
    {
        return CpMenu::class;
    }
}
