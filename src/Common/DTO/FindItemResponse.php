<?php

declare(strict_types=1);

namespace App\Common\DTO;

/**
 * Шаблонный DTO для элемента коллекции.
 */
class FindItemResponse
{
    public function __construct(
        public int $id,
        public string $name,
    ) {
    }
}
