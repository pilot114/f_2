<?php

declare(strict_types=1);

namespace App\Tests\Unit\Common\Attribute;

use App\Common\Attribute\RpcParam;

it('creates RpcParam with default values', function (): void {
    // Arrange & Act
    $param = new RpcParam();

    // Assert
    expect($param->summary)->toBeNull()
        ->and($param->description)->toBeNull()
        ->and($param->required)->toBeTrue()
        ->and($param->deprecated)->toBeFalse()
        ->and($param->schema)->toBeNull()
        ->and($param->schemaName)->toBeNull();
});

it('creates RpcParam with custom summary', function (): void {
    // Arrange & Act
    $param = new RpcParam(summary: 'User ID');

    // Assert
    expect($param->summary)->toBe('User ID')
        ->and($param->description)->toBeNull()
        ->and($param->required)->toBeTrue()
        ->and($param->deprecated)->toBeFalse();
});

it('creates RpcParam with summary and description', function (): void {
    // Arrange & Act
    $param = new RpcParam(
        summary: 'Employee ID',
        description: 'Unique identifier of the employee'
    );

    // Assert
    expect($param->summary)->toBe('Employee ID')
        ->and($param->description)->toBe('Unique identifier of the employee')
        ->and($param->required)->toBeTrue()
        ->and($param->deprecated)->toBeFalse();
});

it('creates optional RpcParam', function (): void {
    // Arrange & Act
    $param = new RpcParam(
        summary: 'Filter',
        required: false
    );

    // Assert
    expect($param->summary)->toBe('Filter')
        ->and($param->required)->toBeFalse();
});

it('creates deprecated RpcParam', function (): void {
    // Arrange & Act
    $param = new RpcParam(
        summary: 'Old field',
        deprecated: true
    );

    // Assert
    expect($param->summary)->toBe('Old field')
        ->and($param->deprecated)->toBeTrue();
});

it('creates RpcParam with all parameters', function (): void {
    // Arrange & Act
    $param = new RpcParam(
        summary: 'Date range',
        description: 'Start and end dates for filtering',
        required: false,
        deprecated: true
    );

    // Assert
    expect($param->summary)->toBe('Date range')
        ->and($param->description)->toBe('Start and end dates for filtering')
        ->and($param->required)->toBeFalse()
        ->and($param->deprecated)->toBeTrue();
});

it('has null schema by default', function (): void {
    // Arrange & Act
    $param = new RpcParam(summary: 'Test param');

    // Assert
    expect($param->schema)->toBeNull()
        ->and($param->schemaName)->toBeNull();
});
