<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Hr\Achievements\Entity;

use App\Domain\Hr\Reinstatement\Entity\Employee;
use DateTimeImmutable;

it('creates entity', function (): void {
    $employee = new Employee(
        id: 22,
        name: 'test',
        department: '1',
        quitDate: new DateTimeImmutable(),
        email: "ee@ma.il",
        login: 'login',
    );

    expect($employee)->toBeInstanceOf(Employee::class);
});
