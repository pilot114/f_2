<?php

declare(strict_types=1);

namespace App\Tests\Unit\Common\Attribute;

use App\Common\Attribute\RpcMethod;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

it('creates RpcMethod with required parameters', function (): void {
    // Arrange & Act
    $method = new RpcMethod(
        name: 'finance.kpi.getList',
        summary: 'Get KPI list'
    );

    // Assert
    expect($method->name)->toBe('finance.kpi.getList')
        ->and($method->summary)->toBe('Get KPI list')
        ->and($method->description)->toBeNull()
        ->and($method->errors)->toBe([])
        ->and($method->examples)->toBe([])
        ->and($method->tags)->toContain('finance', 'kpi')
        ->and($method->isDeprecated)->toBeFalse()
        ->and($method->isAutomapped)->toBeFalse()
        ->and($method->params)->toBe([])
        ->and($method->resultSchema)->toBe([])
        ->and($method->resultSchemaName)->toBeNull();
});

it('creates RpcMethod with all parameters', function (): void {
    // Arrange & Act
    $method = new RpcMethod(
        name: 'hr.achievements.create',
        summary: 'Create achievement',
        description: 'Creates a new achievement for employee',
        errors: ['INVALID_DATA', 'NOT_FOUND'],
        examples: ['example1', 'example2'],
        tags: ['custom-tag'],
        isDeprecated: true,
        isAutomapped: true
    );

    // Assert
    expect($method->name)->toBe('hr.achievements.create')
        ->and($method->summary)->toBe('Create achievement')
        ->and($method->description)->toBe('Creates a new achievement for employee')
        ->and($method->errors)->toBe(['INVALID_DATA', 'NOT_FOUND'])
        ->and($method->examples)->toBe(['example1', 'example2'])
        ->and($method->tags)->toContain('custom-tag', 'hr', 'achievements')
        ->and($method->isDeprecated)->toBeTrue()
        ->and($method->isAutomapped)->toBeTrue();
});

it('automatically adds domain and subdomain to tags', function (): void {
    // Arrange & Act
    $method = new RpcMethod(
        name: 'marketing.calendar.getOffers',
        summary: 'Get offers',
        tags: ['special']
    );

    // Assert
    expect($method->tags)
        ->toContain('special')
        ->toContain('marketing')
        ->toContain('calendar')
        ->toHaveCount(3);
});

it('does not duplicate tags if domain already exists', function (): void {
    // Arrange & Act
    $method = new RpcMethod(
        name: 'finance.reports.export',
        summary: 'Export report',
        tags: ['finance', 'reports']
    );

    // Assert
    $financeTags = array_filter($method->tags, fn ($tag): bool => $tag === 'finance');
    $reportsTags = array_filter($method->tags, fn ($tag): bool => $tag === 'reports');

    expect($financeTags)->toHaveCount(1)
        ->and($reportsTags)->toHaveCount(1);
});

it('throws exception when method name has less than 3 parts', function (): void {
    // Act & Assert
    expect(fn (): RpcMethod => new RpcMethod(
        name: 'finance.kpi',
        summary: 'Invalid'
    ))->toThrow(BadRequestHttpException::class, 'RPC метод должен содержать имя домена, поддомена и юзкейса');
});

it('throws exception when method name has more than 3 parts', function (): void {
    // Act & Assert
    expect(fn (): RpcMethod => new RpcMethod(
        name: 'finance.kpi.get.list',
        summary: 'Invalid'
    ))->toThrow(BadRequestHttpException::class, 'RPC метод должен содержать имя домена, поддомена и юзкейса');
});

it('throws exception when method name has less than 2 parts', function (): void {
    // Act & Assert
    expect(fn (): RpcMethod => new RpcMethod(
        name: 'finance',
        summary: 'Invalid'
    ))->toThrow(BadRequestHttpException::class, 'RPC метод должен содержать имя домена, поддомена и юзкейса');
});

it('identifies query method with get', function (): void {
    // Arrange
    $method = new RpcMethod(
        name: 'finance.kpi.getList',
        summary: 'Get list'
    );

    // Act & Assert
    expect($method->isQuery())->toBeTrue();
});

it('identifies query method with find', function (): void {
    // Arrange
    $method = new RpcMethod(
        name: 'hr.employee.findByName',
        summary: 'Find by name'
    );

    // Act & Assert
    expect($method->isQuery())->toBeTrue();
});

it('identifies query method with search', function (): void {
    // Arrange
    $method = new RpcMethod(
        name: 'marketing.customer.searchHistory',
        summary: 'Search history'
    );

    // Act & Assert
    expect($method->isQuery())->toBeTrue();
});

it('identifies query method with check', function (): void {
    // Arrange
    $method = new RpcMethod(
        name: 'security.user.checkAccess',
        summary: 'Check access'
    );

    // Act & Assert
    expect($method->isQuery())->toBeTrue();
});

it('identifies non-query method', function (): void {
    // Arrange
    $method = new RpcMethod(
        name: 'finance.kpi.create',
        summary: 'Create KPI'
    );

    // Act & Assert
    expect($method->isQuery())->toBeFalse();
});

it('identifies non-query method with update', function (): void {
    // Arrange
    $method = new RpcMethod(
        name: 'hr.employee.updateProfile',
        summary: 'Update profile'
    );

    // Act & Assert
    expect($method->isQuery())->toBeFalse();
});

it('identifies non-query method with delete', function (): void {
    // Arrange
    $method = new RpcMethod(
        name: 'marketing.offer.delete',
        summary: 'Delete offer'
    );

    // Act & Assert
    expect($method->isQuery())->toBeFalse();
});
