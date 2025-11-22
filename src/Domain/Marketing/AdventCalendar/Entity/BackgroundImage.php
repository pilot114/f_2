<?php

declare(strict_types=1);

namespace App\Domain\Marketing\AdventCalendar\Entity;

class BackgroundImage
{
    public function __construct(
        public int $id,
        public string $name,
        public string $url,
    ) {
    }
}
