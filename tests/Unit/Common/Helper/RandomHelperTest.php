<?php

declare(strict_types=1);

namespace App\Tests\Unit\Common\Helper;

use App\Common\Helper\RandomHelper;
use Exception;

it('password generation', function (): void {
    expect(strlen(RandomHelper::generateUserPassword()))->toBe(20)
        ->and(strlen(RandomHelper::generateUserPassword(32)))->toBe(32)
        ->and(RandomHelper::generateUserPassword())->toMatch('/[\w\d!]+/');

});

it('throws exception when password length exceeds 64', function (): void {
    RandomHelper::generateUserPassword(65);
})->throws(Exception::class, 'Password length must be less than 64 symbols.');
