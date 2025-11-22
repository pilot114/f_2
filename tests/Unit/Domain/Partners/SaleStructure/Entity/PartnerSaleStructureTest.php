<?php

declare(strict_types=1);

namespace App\Tests\Unit\Partenrs\SaleStructure\Entity;

use App\Domain\Partners\SaleStructure\Entity\PartnerSaleStructure;

beforeEach(function (): void {
    $this->structure = new PartnerSaleStructure(
        id: 1,
        name: 'Tester land',
        currency: 'euro',
        percent: '11.1',
        points: '22.0',
    );
});

it('creates entity', function (): void {
    expect($this->structure)
        ->getId()->toBe(1)
        ->getName()->toBe('Tester land')
        ->getCurrency()->toBe('euro')
        ->getPercent()->toBe('11.1')
        ->getPoints()->toBe('22.0');
});

it('creates entity from dirty values', function (): void {
    $dirty = PartnerSaleStructure::fromDirtyValues(
        id: 1,
        name: 'TESTer lAnd',
        currency: 'euro',
        percent: 11.1,
        points: 22,
    );
    expect($this->structure)
        ->getId()->toEqual($dirty->getId())
        ->getName()->toEqual($dirty->getName())
        ->getCurrency()->toEqual($dirty->getCurrency())
        ->getPoints()->toEqual($dirty->getPoints())
        ->getPercent()->toEqual($dirty->getPercent());
});
