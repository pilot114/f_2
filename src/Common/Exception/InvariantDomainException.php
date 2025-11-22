<?php

declare(strict_types=1);

namespace App\Common\Exception;

/**
 * Кидается в Entity при попытке нарушить валидность его данных
 *
 * Польза в том, что такие проверки выполняются не только на пользовательском входе (как в DTO)
 * а также и на данных из БД (будет видно если в БД кто-нибудь поломает данные)
 */
class InvariantDomainException extends DomainException
{
    public function __construct(string $message)
    {
        parent::__construct($message, DomainException::INVARIANT);
    }
}
