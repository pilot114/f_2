<?php

declare(strict_types=1);

namespace App\Domain\Marketing\CustomerHistory\Entity;

use Database\ORM\Attribute\Column;

readonly class Country
{
    public const TABLE = 'net.nc_story_of_customer';
    public function __construct(
        #[Column(name: 'id')] public ?string $id,
        #[Column(name: 'name')] public ?string $name,
    ) {
    }
}
