<?php

declare(strict_types=1);

namespace App\Tests\Unit\Events\Rewards\UseCase;

use App\Common\Helper\EnumerableWithTotal;
use App\Domain\Events\Rewards\Entity\Country;
use App\Domain\Events\Rewards\Repository\CountryQueryRepository;
use App\Domain\Events\Rewards\UseCase\GetCountryListUseCase;
use Illuminate\Support\Collection;
use Mockery;

it('get country list', function (): void {
    $repository = Mockery::mock(CountryQueryRepository::class);
    $useCase = new GetCountryListUseCase($repository);

    $countries = EnumerableWithTotal::build([
        new Country(1, 'Страна 1'),
        new Country(2, 'Страна 2'),
        new Country(3, 'Страна 3'),
    ]);

    $repository->shouldReceive('findAll')->andReturn($countries);

    $result = $useCase->getCountryList();

    expect($result)->toBeInstanceOf(Collection::class);
    expect($result->count())->toBe($countries->count());
});
