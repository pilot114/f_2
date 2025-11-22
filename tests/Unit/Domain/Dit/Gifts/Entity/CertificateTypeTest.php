<?php

declare(strict_types=1);

namespace App\tests\Unit\Dit\Gifts\Entity;

use App\Domain\Dit\Gifts\Entity\CertificateType;

it('creates certificate type with all fields', function (): void {
    $certificateType = new CertificateType(
        id: 123,
        name: 'Premium Certificate'
    );

    expect($certificateType->id)->toBe(123);
    expect($certificateType->name)->toBe('Premium Certificate');
});

it('converts to array correctly', function (): void {
    $certificateType = new CertificateType(
        id: 456,
        name: 'Basic Certificate'
    );

    $array = $certificateType->toArray();

    expect($array)->toBe([
        'id'   => 456,
        'name' => 'Basic Certificate',
    ]);
});
