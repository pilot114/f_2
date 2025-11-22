<?php

declare(strict_types=1);

namespace App\Domain\Portal\Files\Enum;

enum AllowedImageType: string
{
    case JPEG = 'jpeg';
    case JPG = 'jpg';
    case PNG = 'png';
    case GIF = 'gif';
    case BMP = 'bmp';
    case WEBP = 'webp';
}
