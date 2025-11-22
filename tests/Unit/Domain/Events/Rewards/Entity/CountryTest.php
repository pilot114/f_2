<?php

declare(strict_types=1);

namespace App\Tests\Unit\Events\Rewards\Entity;

use App\Domain\Events\Rewards\Entity\Country;

it('create country', function (): void {
    $countryId = 9;
    $countryName = 'name';
    $country = new Country($countryId, $countryName);
    $countryArray = $country->toArray();

    expect($country->name)->toBe($countryName);
    expect($countryArray['id'])->toBe($countryId);
    expect($countryArray['name'])->toBe($countryName);
});
