<?php

declare(strict_types=1);

if (!function_exists('assertSnapshot')) {
    function assertSnapshot(string $name, string $content): void
    {
        $snapshotPath = __DIR__ . '/snapshots/' . $name;

        if (!file_exists($snapshotPath)) {
            file_put_contents($snapshotPath, $content);
            test()->markTestIncomplete("Snapshot created for $name");
        }

        expect($content)->toBe(file_get_contents($snapshotPath));
    }
}
