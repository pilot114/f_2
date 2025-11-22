<?php

declare(strict_types=1);

namespace App\Common\Exception;

abstract class DomainException extends \DomainException
{
    public const INVARIANT = 601;
    public const STATIC = 602;
    public const FILE = 603;

    protected array $context = [];

    public function __construct(string $message = "", int $code = 0, array $context = [])
    {
        parent::__construct($message, $code);
        $this->context = $context;
    }

    public function getContext(): array
    {
        return $this->context;
    }
}
