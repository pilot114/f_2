<?php

declare(strict_types=1);

namespace App\Domain\Portal\Files\Enum;

enum ImageResizeType: string
{
    case FIT = 'fit';   // Пропорциональное изменение размера
    case CROP = 'crop'; // Обрезка картинки по размеру от центра картинки
}
