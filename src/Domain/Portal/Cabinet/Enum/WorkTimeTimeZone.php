<?php

declare(strict_types=1);

namespace App\Domain\Portal\Cabinet\Enum;

use App\Common\DTO\Titleable;

enum WorkTimeTimeZone: string implements Titleable
{
    case NOVOSIBIRSK = "Asia/Novosibirsk";
    case MOSCOW = "Europe/Moscow";

    public function getTitle(): string
    {
        return match ($this) {
            self::NOVOSIBIRSK => 'по новосибирскому времени',
            self::MOSCOW      => 'по московскому времени',
        };
    }
}
