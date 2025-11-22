<?php

declare(strict_types=1);

namespace App\Tests\Unit\User\Cabinet\Entity;

use App\Domain\Portal\Cabinet\Entity\Congratulation;

it('change user avatar', function (Congratulation $congratulation): void {
    $expected = [
        'id'             => $congratulation->getId(),
        'from_user_id'   => $congratulation->getFromUserId(),
        'from_user_name' => $congratulation->getFromUserName(),
        'message'        => $congratulation->getMessage(),
        'year'           => $congratulation->getYear()->format('Y'),
        'avatar'         => $congratulation->getAvatar(),
    ];
    $result = $congratulation->toArray();

    expect($result)->toBe($expected);

})->with('congratulations');
