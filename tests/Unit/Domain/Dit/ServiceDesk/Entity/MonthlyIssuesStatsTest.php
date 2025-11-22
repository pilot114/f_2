<?php

declare(strict_types=1);

namespace App\tests\Unit\Dit\ServiceDesk\Entity;

use App\Domain\Dit\ServiceDesk\Entity\MonthlyIssuesStats;
use DateTimeImmutable;

it('can be created with required parameters', function (): void {
    $month = new DateTimeImmutable('2024-01-01');
    $createdIssues = 15;
    $resolvedIssues = 12;

    $stats = new MonthlyIssuesStats(
        month: $month,
        createdIssues: $createdIssues,
        resolvedIssues: $resolvedIssues
    );

    expect($stats->month)->toBe($month);
    expect($stats->createdIssues)->toBe($createdIssues);
    expect($stats->resolvedIssues)->toBe($resolvedIssues);
});

it('returns correct array representation', function (): void {
    $month = new DateTimeImmutable('2024-01-15 10:30:00');
    $createdIssues = 25;
    $resolvedIssues = 18;

    $stats = new MonthlyIssuesStats(
        month: $month,
        createdIssues: $createdIssues,
        resolvedIssues: $resolvedIssues
    );

    $array = $stats->toArray();

    expect($array)->toHaveKeys(['month', 'createdIssues', 'resolvedIssues']);
    expect($array['month'])->toBe($month->format(DateTimeImmutable::ATOM));
    expect($array['createdIssues'])->toBe($createdIssues);
    expect($array['resolvedIssues'])->toBe($resolvedIssues);
});

it('handles zero values correctly', function (): void {
    $month = new DateTimeImmutable('2024-02-01');
    $stats = new MonthlyIssuesStats(
        month: $month,
        createdIssues: 0,
        resolvedIssues: 0
    );

    expect($stats->createdIssues)->toBe(0);
    expect($stats->resolvedIssues)->toBe(0);

    $array = $stats->toArray();
    expect($array['createdIssues'])->toBe(0);
    expect($array['resolvedIssues'])->toBe(0);
});

it('handles large values correctly', function (): void {
    $month = new DateTimeImmutable('2024-03-01');
    $createdIssues = 999999;
    $resolvedIssues = 888888;

    $stats = new MonthlyIssuesStats(
        month: $month,
        createdIssues: $createdIssues,
        resolvedIssues: $resolvedIssues
    );

    expect($stats->createdIssues)->toBe($createdIssues);
    expect($stats->resolvedIssues)->toBe($resolvedIssues);
});
