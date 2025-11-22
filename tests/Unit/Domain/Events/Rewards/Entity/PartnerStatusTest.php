<?php

declare(strict_types=1);

namespace App\Tests\Unit\Events\Rewards\Entity;

use App\Domain\Events\Rewards\Entity\PartnerStatus;
use App\Domain\Events\Rewards\Entity\User;
use App\Domain\Events\Rewards\Enum\PartnerStatusType;

it('creates partner status', function (): void {
    $status = new PartnerStatus(
        id: 1,
        partnerId: 100,
        statusType: PartnerStatusType::NOT_VERIFIED,
        rewardsCount: 5,
        penaltiesCount: 2
    );

    expect($status->id)->toBe(1);
    expect($status->partnerId)->toBe(100);
    expect($status->getStatusType())->toBe(PartnerStatusType::NOT_VERIFIED);
    expect($status->getRewardsCount())->toBe(5);
    expect($status->getPenaltiesCount())->toBe(2);
});

it('gets status name', function (PartnerStatusType $statusType, string $expectedName): void {
    $status = new PartnerStatus(
        id: 1,
        partnerId: 100,
        statusType: $statusType,
        rewardsCount: 0,
        penaltiesCount: 0
    );

    expect($status->getName())->toBe($expectedName);
})->with([
    [PartnerStatusType::NOT_VERIFIED, 'Не проверен'],
    [PartnerStatusType::TO_AWARD, 'К выдаче'],
    [PartnerStatusType::NOT_AWARDED, 'Не награждается'],
    [PartnerStatusType::EXCLUDED, 'Исключен'],
]);

it('sets and gets status type', function (): void {
    $status = new PartnerStatus(
        id: 1,
        partnerId: 100,
        statusType: PartnerStatusType::NOT_VERIFIED,
        rewardsCount: 0,
        penaltiesCount: 0
    );

    expect($status->getStatusType())->toBe(PartnerStatusType::NOT_VERIFIED);

    $status->setStatusType(PartnerStatusType::TO_AWARD);

    expect($status->getStatusType())->toBe(PartnerStatusType::TO_AWARD);
});

it('sets and gets rewards count', function (): void {
    $status = new PartnerStatus(
        id: 1,
        partnerId: 100,
        statusType: PartnerStatusType::NOT_VERIFIED,
        rewardsCount: 5,
        penaltiesCount: 0
    );

    expect($status->getRewardsCount())->toBe(5);

    $status->setRewardsCount(10);

    expect($status->getRewardsCount())->toBe(10);
});

it('sets and gets penalties count', function (): void {
    $status = new PartnerStatus(
        id: 1,
        partnerId: 100,
        statusType: PartnerStatusType::NOT_VERIFIED,
        rewardsCount: 0,
        penaltiesCount: 3
    );

    expect($status->getPenaltiesCount())->toBe(3);

    $status->setPenaltiesCount(7);

    expect($status->getPenaltiesCount())->toBe(7);
});

it('sets and gets user', function (): void {
    $status = new PartnerStatus(
        id: 1,
        partnerId: 100,
        statusType: PartnerStatusType::NOT_VERIFIED,
        rewardsCount: 0,
        penaltiesCount: 0
    );

    expect($status->getUser())->toBeNull();

    $user = new User(1, 'Администратор');
    $status->setUser($user);

    expect($status->getUser())->toBe($user);

    $status->setUser(null);

    expect($status->getUser())->toBeNull();
});

it('converts to partner status response', function (): void {
    $status = new PartnerStatus(
        id: 1,
        partnerId: 100,
        statusType: PartnerStatusType::TO_AWARD,
        rewardsCount: 5,
        penaltiesCount: 2
    );

    $result = $status->toPartnerStatusResponse();

    expect($result->id)->toBe(1);
    expect($result->statusType->id)->toBe(2);
    expect($result->statusType->name)->toBe('К выдаче');
    expect($result->rewardsCount)->toBe(5);
    expect($result->penaltiesCount)->toBe(2);
});

it('handles null counts in response', function (): void {
    $status = new PartnerStatus(
        id: 1,
        partnerId: 100,
        statusType: PartnerStatusType::NOT_VERIFIED,
        rewardsCount: null,
        penaltiesCount: null
    );

    expect($status->getRewardsCount())->toBeNull();
    expect($status->getPenaltiesCount())->toBeNull();

    $result = $status->toPartnerStatusResponse();
    expect($result->rewardsCount)->toBeNull();
    expect($result->penaltiesCount)->toBeNull();
});
