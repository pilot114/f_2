<?php

declare(strict_types=1);

namespace App\Common\Exception;

use Exception;

final class ProductImageException extends Exception
{
    public function __construct(string $message)
    {
        parent::__construct("Ошибка получения изображений: {$message}", 400);
    }
}
