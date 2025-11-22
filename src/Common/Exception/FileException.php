<?php

declare(strict_types=1);

namespace App\Common\Exception;

class FileException extends DomainException
{
    public function __construct(string $message)
    {
        parent::__construct($message, DomainException::FILE);
    }
}
