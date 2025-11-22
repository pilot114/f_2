<?php

declare(strict_types=1);

namespace App\tests\Unit\Dit\ServiceDesk\Entity;

use App\Domain\Dit\ServiceDesk\Entity\YearlyRatingsStats;

it('can be created with all parameters', function (): void {
    $year = 2024;
    $averageRating = 4.5;
    $ratingsCount = 150;

    $stats = new YearlyRatingsStats(
        year: $year,
        averageRating: $averageRating,
        ratingsCount: $ratingsCount
    );

    expect($stats->year)->toBe($year);
    expect($stats->averageRating)->toBe($averageRating);
    expect($stats->ratingsCount)->toBe($ratingsCount);
});

it('can be created with null values', function (): void {
    $year = 2024;

    $stats = new YearlyRatingsStats(
        year: $year,
        averageRating: null,
        ratingsCount: null
    );

    expect($stats->year)->toBe($year);
    expect($stats->averageRating)->toBeNull();
    expect($stats->ratingsCount)->toBeNull();
});

it('returns correct array representation with all values', function (): void {
    $year = 2023;
    $averageRating = 3.75;
    $ratingsCount = 200;

    $stats = new YearlyRatingsStats(
        year: $year,
        averageRating: $averageRating,
        ratingsCount: $ratingsCount
    );

    $array = $stats->toArray();

    expect($array)->toHaveKeys(['year', 'averageRating', 'ratingsCount']);
    expect($array['year'])->toBe($year);
    expect($array['averageRating'])->toBe($averageRating);
    expect($array['ratingsCount'])->toBe($ratingsCount);
});

it('returns correct array representation with null values', function (): void {
    $year = 2022;

    $stats = new YearlyRatingsStats(
        year: $year,
        averageRating: null,
        ratingsCount: null
    );

    $array = $stats->toArray();

    expect($array)->toHaveKeys(['year', 'averageRating', 'ratingsCount']);
    expect($array['year'])->toBe($year);
    expect($array['averageRating'])->toBeNull();
    expect($array['ratingsCount'])->toBeNull();
});

it('handles zero values correctly', function (): void {
    $year = 2021;
    $averageRating = 0.0;
    $ratingsCount = 0;

    $stats = new YearlyRatingsStats(
        year: $year,
        averageRating: $averageRating,
        ratingsCount: $ratingsCount
    );

    expect($stats->averageRating)->toBe(0.0);
    expect($stats->ratingsCount)->toBe(0);

    $array = $stats->toArray();
    expect($array['averageRating'])->toBe(0.0);
    expect($array['ratingsCount'])->toBe(0);
});

it('handles edge rating values correctly', function (): void {
    $year = 2020;
    $minRating = 1.0;
    $maxRating = 5.0;

    $statsMin = new YearlyRatingsStats(year: $year, averageRating: $minRating, ratingsCount: 1);
    $statsMax = new YearlyRatingsStats(year: $year, averageRating: $maxRating, ratingsCount: 1);

    expect($statsMin->averageRating)->toBe($minRating);
    expect($statsMax->averageRating)->toBe($maxRating);
});
