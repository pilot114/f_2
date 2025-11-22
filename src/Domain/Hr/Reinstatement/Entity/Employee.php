<?php

declare(strict_types=1);

namespace App\Domain\Hr\Reinstatement\Entity;

use Database\ORM\Attribute\Column;
use Database\ORM\Attribute\Entity;
use DateTimeImmutable;

#[Entity(name: 'TEST.CP_EMP', sequenceName: 'TEST.CP_EMP_SQ')]
class Employee
{
    public function __construct(
        #[Column(name: 'id')] public readonly int           $id,
        #[Column(name: 'emp_name')] public string     $name,
        #[Column(name: 'emp_department_name')] public string $department,
        #[Column(name: 'emp_dismiss_dt')] public ?DateTimeImmutable     $quitDate,
        #[Column(name: 'emp_email')] public string     $email,
        #[Column(name: 'emp_login')] public string     $login,
    ) {
    }
}
