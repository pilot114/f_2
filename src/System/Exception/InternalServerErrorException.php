<?php

declare(strict_types=1);

namespace App\System\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

class InternalServerErrorException extends HttpException
{
    public function __construct(string $message = 'Внутренняя ошибка сервера')
    {
        parent::__construct(500, $message);
    }
}
