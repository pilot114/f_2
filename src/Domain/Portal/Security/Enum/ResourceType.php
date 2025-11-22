<?php

declare(strict_types=1);

namespace App\Domain\Portal\Security\Enum;

enum ResourceType: string
{
    case CP_ACTION = 'cp_action';
    case CP_MENU = 'cp_menu';
}
