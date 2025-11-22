<?php

declare(strict_types=1);

namespace App\Tests\Unit\User\Cabinet\Entity;

use App\Common\Exception\InvariantDomainException;
use App\Domain\Portal\Cabinet\Entity\Password;

it('change valid password', function (): void {
    [$userId, $old, $new] = [9999, '12345', 'asd9ASD{}asd'];

    $password = new Password($userId, $old);
    $password->changePassword($old, $new);

    expect($password->getPassword())->toBe($new);
});

it('change password old isWrong', function (): void {
    [$userId, $old, $new] = [9999, '12345', 'asd9ASD{}asd'];

    $this->expectException(InvariantDomainException::class);

    $password = new Password($userId, '678910');
    $password->changePassword($old, $new);
});

it('change password old and new match', function (): void {
    [$userId, $old, $new] = [9999, '12345', 'asd9ASD{}asd'];

    $this->expectException(InvariantDomainException::class);

    $password = new Password($userId, $new);
    $password->changePassword($new, $new);
});
