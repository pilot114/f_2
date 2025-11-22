<?php

declare(strict_types=1);

namespace App\Tests\Unit\Marketing\AdventCalendar\Entity;

use App\Domain\Marketing\AdventCalendar\Entity\BackgroundImage;

describe('BackgroundImage', function (): void {
    it('can be instantiated with all parameters', function (): void {
        $image = new BackgroundImage(
            id: 1,
            name: 'Background Image',
            url: 'https://example.com/bg.jpg'
        );

        expect($image->id)->toBe(1)
            ->and($image->name)->toBe('Background Image')
            ->and($image->url)->toBe('https://example.com/bg.jpg');
    });
});
