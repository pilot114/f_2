<?php

declare(strict_types=1);

namespace App\Domain\Marketing\CustomerHistory\Entity;

use Database\ORM\Attribute\Column;
use Database\ORM\Attribute\Entity;

#[Entity(name: ProductCountry::TABLE)]
readonly class ProductCountry
{
    public const TABLE = 'test.nc_product_country_langs';

    public function __construct(
        #[Column(name: 'id')] public string $id,
        #[Column(name: 'name_ru')] public string $name,
    ) {
    }
}
