<?php

declare(strict_types=1);

namespace App\Tests\Unit\Partenrs\SaleStructure\Entity;

use App\Common\Helper\DateHelper;
use DateTimeImmutable;

beforeEach(function (): void {
    $this->monthGetter = new DateHelper(
        dateString: '2025-01-15',
    );
});

it('creates entity', function (): void {
    expect($this->monthGetter)
        ->getDate()->toBeInstanceOf(DateTimeImmutable::class);
});

it('format russian date', function (): void {
    expect($this->monthGetter)
        ->getRussianMonthAndYear()->toBe('Январь 2025');
});
