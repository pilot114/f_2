<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\OperationalEfficiency\DDMRP\Entity;

use App\Domain\OperationalEfficiency\DDMRP\Entity\DdmrpEmployeeAccess;

it('creates ddmrp employee access', function (): void {
    $access = new DdmrpEmployeeAccess(
        id: 1,
        contract: 'TEST-001',
        cpEmpId: 999
    );

    expect($access->getId())->toBe(1);
});

it('returns correct id', function (): void {
    $access = new DdmrpEmployeeAccess(
        id: 50,
        contract: 'TEST-002',
        cpEmpId: 888
    );

    expect($access->getId())->toBe(50);
});
