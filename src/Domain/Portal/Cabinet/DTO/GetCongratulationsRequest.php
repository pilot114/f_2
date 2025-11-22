<?php

declare(strict_types=1);

namespace App\Domain\Portal\Cabinet\DTO;

use DateTimeImmutable;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Throwable;

readonly class GetCongratulationsRequest
{
    public function __construct(
        #[Assert\NotBlank]
        public int     $receiverId,
        #[Assert\Callback([self::class, 'validateDate'])]
        public ?string $startFrom,
    ) {
    }

    public static function validateDate(?string $startFrom, ExecutionContextInterface $context): void
    {
        if ($startFrom !== null) {
            try {
                new DateTimeImmutable($startFrom);
            } catch (Throwable) {
                $context->buildViolation('"startFrom" должен быть в формате даты')
                    ->atPath('birthday')
                    ->addViolation();
            }
        }
    }
}
