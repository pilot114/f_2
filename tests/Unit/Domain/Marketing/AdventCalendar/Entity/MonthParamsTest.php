<?php

declare(strict_types=1);

namespace App\Tests\Unit\Marketing\AdventCalendar\Entity;

use App\Domain\Marketing\AdventCalendar\Entity\MonthLanguage;
use App\Domain\Marketing\AdventCalendar\Entity\MonthParams;

describe('MonthParams', function (): void {
    it('can be instantiated with basic parameters', function (): void {
        $params = new MonthParams(
            year: 2024,
            month: 12,
            name: 'December',
            langs: []
        );

        expect($params->year)->toBe(2024)
            ->and($params->month)->toBe(12)
            ->and($params->name)->toBe('December')
            ->and($params->langs)->toBe([]);
    });

    it('can be instantiated with multiple languages', function (): void {
        $langRu = new MonthLanguage('ru', 'Декабрь', 'Дек', true);
        $langEn = new MonthLanguage('en', 'December', 'Dec', false);

        $params = new MonthParams(
            year: 2024,
            month: 12,
            name: 'December',
            langs: [$langRu, $langEn]
        );

        expect($params->langs)->toHaveCount(2)
            ->and($params->langs[0])->toBe($langRu)
            ->and($params->langs[1])->toBe($langEn);
    });
});
