<?php

declare(strict_types=1);

namespace App\System\Exception;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class BadRequestHttpExceptionWithViolations extends BadRequestHttpException
{
    public function __construct(
        protected ConstraintViolationListInterface $violations
    ) {
        parent::__construct('Невалидные данные');
    }

    public function getViolations(): array
    {
        $errors = [];
        foreach ($this->violations as $violation) {
            $errors[] = [
                'field'   => $violation->getPropertyPath(),
                'message' => $violation->getMessage(),
            ];
        }
        return $errors;
    }
}
