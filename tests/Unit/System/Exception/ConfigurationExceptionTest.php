<?php

declare(strict_types=1);

use App\System\Exception\ConfigurationException;

it('is a runtime exception', function (): void {
    $exception = new ConfigurationException('Test message');

    expect($exception)->toBeInstanceOf(RuntimeException::class);
    expect($exception->getMessage())->toBe('Test message');
});

it('can be thrown and caught', function (): void {
    expect(fn () => throw new ConfigurationException('Configuration error'))
        ->toThrow(ConfigurationException::class, 'Configuration error');
});
