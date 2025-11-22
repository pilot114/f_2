<?php

declare(strict_types=1);

namespace App\Tests\Unit\Partenrs\SaleStructure\Entity;

use App\Domain\Partners\SaleStructure\Entity\PartnerInfo;
use DateTimeImmutable;

beforeEach(function (): void {
    $this->entity = new PartnerInfo(
        id: 1,
        name: "Test U. Ser",
        contract: '0303666',
        countryName: 'Testerstan',
        countryCode: 422,
        rank: 1,
        dateEnd: new DateTimeImmutable('2025-12-01'),
        dateRankAssigned: new DateTimeImmutable('2025-01-01'),
        rankName: "Test rang",
    );
});

it('creates entity', function (): void {
    expect($this->entity)
        ->getId()->toBe(1)
        ->getName()->toBe('Test U. Ser')
        ->getContract()->toBe('0303666')
        ->getCountryName()->toBe('Testerstan')
        ->getCountryCode()->toBe(422)
        ->getRank()->toBe(1)
        ->getDateEnd()->format('Ymd')->toBe((new DateTimeImmutable('2025-12-01'))->format('Ymd'))
        ->getDateRankAssigned()->format('Ymd')->toBe((new DateTimeImmutable('2025-01-01'))->format('Ymd'))
        ->getRankName()->toBe("Test rang");
});
