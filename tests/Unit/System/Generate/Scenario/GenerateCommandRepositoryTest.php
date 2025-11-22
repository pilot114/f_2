<?php

declare(strict_types=1);

use App\System\Generate\Scenario\GenerateCommandRepository;

require_once __DIR__ . '/SnapshotHelper.php';

it('generates command repository correctly', function (): void {
    $repoName = 'TestCommandRepository';

    $generator = new GenerateCommandRepository($repoName);
    $generator->load();
    $files = iterator_to_array($generator->run('App\Domain\Test\Repository'));

    expect($files)->toHaveKey('TestCommandRepository.php');

    assertSnapshot('TestCommandRepository.php.snapshot', $files['TestCommandRepository.php']);
});

it('can be instantiated with correct parameters', function (): void {
    $generator = new GenerateCommandRepository('TestRepository');

    expect($generator)->toBeInstanceOf(GenerateCommandRepository::class);
});

it('load method works without errors', function (): void {
    $generator = new GenerateCommandRepository('TestRepository');

    // load() should work without throwing exceptions
    $generator->load();

    expect($generator)->toBeInstanceOf(GenerateCommandRepository::class);
});
