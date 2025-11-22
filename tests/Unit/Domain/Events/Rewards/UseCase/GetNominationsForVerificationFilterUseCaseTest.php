<?php

declare(strict_types=1);

namespace App\Tests\Unit\Events\Rewards\UseCase;

use App\Domain\Events\Rewards\Entity\Nomination;
use App\Domain\Events\Rewards\Entity\Program;
use App\Domain\Events\Rewards\Repository\NominationQueryRepository;
use App\Domain\Events\Rewards\UseCase\GetNominationsForVerificationFilterUseCase;
use Mockery;

beforeEach(function (): void {
    $this->repository = Mockery::mock(NominationQueryRepository::class);
    $this->useCase = new GetNominationsForVerificationFilterUseCase($this->repository);
});

afterEach(function (): void {
    Mockery::close();
});

it('create group', function (): void {

    $programId = 1;
    $programName = 'test';
    $program = new Program($programId, $programName);

    $nominationId = 1;
    $nominationName = 'test';
    $nomination = new Nomination($nominationId, $nominationName, $program);

    $nominations = collect();
    $nominations->add(
        $nomination
    );

    $this->repository->shouldReceive('getNominationsForVerificationFilter')
        ->once()
        ->andReturn($nominations);

    $programIds = [1];
    $result = $this->useCase->getNominationsForVerificationFilter($programIds);
    expect($result->first())->toBeInstanceOf(Nomination::class);
});
