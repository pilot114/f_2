<?php

declare(strict_types=1);

namespace App\Domain\Marketing\CustomerHistory\Enum;

use App\Common\DTO\Titleable;

enum Status: int implements Titleable
{
    case MODERATION = 1;
    case PUBLISHED = 2;
    case REFUSED = 3;

    public function getTitle(): string
    {
        return match ($this) {
            self::MODERATION => 'На модерации',
            self::PUBLISHED  => 'Опубликовано',
            self::REFUSED    => 'Отказано',
        };
    }
}
