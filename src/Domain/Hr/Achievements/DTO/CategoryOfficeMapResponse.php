<?php

declare(strict_types=1);

namespace App\Domain\Hr\Achievements\DTO;

use App\Common\Attribute\RpcParam;

readonly class CategoryOfficeMapResponse
{
    public function __construct(
        public int $id,
        public string $name,
        public bool $isPersonal,
        public ?ColorResponse $color,
        #[RpcParam('Общее количество карточек в категории')]
        public int $cardCount,
        public array $locked = [],
        public array $unlocked = [],
    ) {
    }
}
