<?php

declare(strict_types=1);

namespace App\Tests\Unit\Events\Rewards\Entity;

use App\Common\Exception\InvariantDomainException;
use App\Domain\Events\Rewards\DTO\GroupWithProgramsResponse;
use App\Domain\Events\Rewards\DTO\ProgramWithNominationsResponse;
use App\Domain\Events\Rewards\Entity\Group;
use App\Domain\Events\Rewards\Entity\Nomination;
use App\Domain\Events\Rewards\Entity\Program;
use App\Domain\Events\Rewards\Enum\GroupType;

it('create group', function (): void {
    $groupId = 1;
    $groupName = 'test';
    $group = new Group($groupId, $groupName, [], GroupType::GROUP);
    $groupResponse = $group->toGroupWithProgramsResponse();

    expect($groupResponse->id)->toBe($groupId);
    expect($groupResponse->name)->toBe($groupName);
    expect($groupResponse->programs_count)->toBe(0);
    expect($groupResponse->programs)->toBe([]);
});

it('toGroupWithProgramsResponse creates correct DTO', function (): void {
    $program1 = new Program(1, 'Program 1');
    $program2 = new Program(2, 'Program 2');

    $nomination1 = new Nomination(1, 'Nomination 1', $program1, []);
    $nomination2 = new Nomination(2, 'Nomination 2', $program2, []);

    $program1->setNominations([$nomination1]);
    $program2->setNominations([$nomination2]);

    $group = new Group(1, 'Test Group', [$program1, $program2], GroupType::GROUP);

    $response = $group->toGroupWithProgramsResponse();

    expect($response)->toBeInstanceOf(GroupWithProgramsResponse::class)
        ->and($response->id)->toBe(1)
        ->and($response->name)->toBe('Test Group')
        ->and($response->programs_count)->toBe(2)
        ->and($response->programs)->toHaveCount(2)
        ->and($response->programs[0])->toBeInstanceOf(ProgramWithNominationsResponse::class)
        ->and($response->programs[0]->name)->toBe('Program 1');
});

it('unallocated name', function (): void {
    $category = new Group(0, '');

    expect($category->getName())->toBe(Group::UNALLOCATED_PROGRAMS_NAME);
});

it('create group wrong type', function (): void {
    $groupId = 1;
    $groupName = 'test';

    $this->expectException(InvariantDomainException::class);
    $this->expectExceptionMessage("параметр type для группы может быть равен только 1");

    new Group($groupId, $groupName, [], GroupType::CATEGORY);
});
