<?php

declare(strict_types=1);

namespace App\Domain\Portal\Files\Enum;

enum ImageSize: int
{
    case MINI = 60;
    case SMALL = 80;
    case MEDIUM = 400;
    case BIG = 800;
}
