<?php

declare(strict_types=1);

namespace App\Common\Exception;

class StaticException extends DomainException
{
    public function __construct(
        public array $data
    ) {
        $message = sprintf(
            'Ошибка работы с сервером статики: %s (%s)',
            $data['name'] ?? 'Имя не задано',
            $data['message'] ?? 'Неизвестная ошибка'
        );
        parent::__construct($message, DomainException::STATIC);
    }
}
