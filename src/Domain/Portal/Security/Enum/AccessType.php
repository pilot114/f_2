<?php

declare(strict_types=1);

namespace App\Domain\Portal\Security\Enum;

enum AccessType: string
{
    case EXECUTE = 'execute';
    case READ = 'read';
    case WRITE = 'write';
}
