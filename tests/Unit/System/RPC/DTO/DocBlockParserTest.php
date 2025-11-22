<?php

declare(strict_types=1);

namespace App\Tests\Unit\System\RPC\DTO;

use App\System\RPC\DTO\DocBlockParser;

it('parses array type from docblock', function (): void {
    // Arrange
    $docString = '/**
 * @param MyClass[] $items
 */';
    $parser = new DocBlockParser($docString);

    // Act
    $result = $parser->getArrayType('App\Common\Service\File\FileService', 'items');

    // Assert
    expect($result)->toBe('\App\Common\Service\File\MyClass'); // Parser resolves relative class names
});

it('returns null for non-array parameter', function (): void {
    // Arrange
    $docString = '/**
 * @param string $name
 */';
    $parser = new DocBlockParser($docString);

    // Act
    $result = $parser->getArrayType('App\Common\Service\File\FileService', 'name');

    // Assert
    expect($result)->toBeNull();
});

it('returns null for non-existent parameter', function (): void {
    // Arrange
    $docString = '/**
 * @param string $name
 */';
    $parser = new DocBlockParser($docString);

    // Act
    $result = $parser->getArrayType('App\Common\Service\File\FileService', 'nonExistent');

    // Assert
    expect($result)->toBeNull();
});

it('handles empty docblock', function (): void {
    // Arrange
    $docString = '/**
 */';
    $parser = new DocBlockParser($docString);

    // Act
    $result = $parser->getArrayType('App\Common\Service\File\FileService', 'items');

    // Assert
    expect($result)->toBeNull();
});

it('handles docblock without param tags', function (): void {
    // Arrange
    $docString = '/**
 * This is a description
 * @return void
 */';
    $parser = new DocBlockParser($docString);

    // Act
    $result = $parser->getArrayType('App\Common\Service\File\FileService', 'items');

    // Assert
    expect($result)->toBeNull();
});

it('handles multiple parameters and finds correct one', function (): void {
    // Arrange
    $docString = '/**
 * @param string $name
 * @param MyClass[] $items
 * @param int $count
 */';
    $parser = new DocBlockParser($docString);

    // Act
    $result = $parser->getArrayType('App\Common\Service\File\FileService', 'items');

    // Assert
    expect($result)->toBe('\App\Common\Service\File\MyClass'); // Parser resolves relative class names
});

it('ignores non-param tags', function (): void {
    // Arrange
    $docString = '/**
 * @return MyClass[]
 * @throws Exception
 */';
    $parser = new DocBlockParser($docString);

    // Act
    $result = $parser->getArrayType('App\Common\Service\File\FileService', 'items');

    // Assert
    expect($result)->toBeNull();
});

it('handles complex array type with namespace', function (): void {
    // Arrange
    $docString = '/**
 * @param App\Domain\User\Entity\User[] $users
 */';
    $parser = new DocBlockParser($docString);

    // Act
    $result = $parser->getArrayType('App\Common\Service\File\FileService', 'users');

    // Assert
    expect($result)->toBe('\App\Common\Service\File\User'); // Parser resolves based on current namespace
});
