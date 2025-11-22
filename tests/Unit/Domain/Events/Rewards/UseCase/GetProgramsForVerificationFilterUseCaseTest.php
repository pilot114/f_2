<?php

declare(strict_types=1);

use App\Domain\Events\Rewards\Entity\Program;
use App\Domain\Events\Rewards\Repository\ProgramQueryRepository;
use App\Domain\Events\Rewards\UseCase\GetProgramsForVerificationFilterUseCase;
use Illuminate\Support\Collection;

describe('GetProgramsForVerificationFilterUseCase', function (): void {
    it('retrieves programs for verification filter from repository', function (): void {
        // Create mock programs
        $program1 = new Program(1, 'Program 1');
        $program2 = new Program(2, 'Program 2');
        $expectedPrograms = collect([$program1, $program2]);

        // Mock repository
        $repository = mock(ProgramQueryRepository::class);
        $repository->shouldReceive('getProgramsForVerificationFilter')
            ->once()
            ->andReturn($expectedPrograms);

        // Create use case with mocked repository
        $useCase = new GetProgramsForVerificationFilterUseCase($repository);

        // Execute the use case
        $result = $useCase->getList();

        // Assert result is as expected
        expect($result)->toBe($expectedPrograms)
            ->and($result)->toBeInstanceOf(Collection::class)
            ->and($result)->toHaveCount(2);

        // Verify first program
        $firstProgram = $result->first();
        expect($firstProgram)->toBeInstanceOf(Program::class)
            ->and($firstProgram->id)->toBe(1)
            ->and($firstProgram->name)->toBe('Program 1');

        // Verify second program
        $secondProgram = $result->skip(1)->first();
        expect($secondProgram)->toBeInstanceOf(Program::class)
            ->and($secondProgram->id)->toBe(2)
            ->and($secondProgram->name)->toBe('Program 2');
    });

    it('passes through empty results from repository', function (): void {
        // Mock repository to return empty collection
        $repository = mock(ProgramQueryRepository::class);
        $repository->shouldReceive('getProgramsForVerificationFilter')
            ->once()
            ->andReturn(collect([]));

        // Create use case with mocked repository
        $useCase = new GetProgramsForVerificationFilterUseCase($repository);

        // Execute the use case
        $result = $useCase->getList();

        // Assert result is an empty collection
        expect($result)->toBeInstanceOf(Collection::class)
            ->and($result)->toBeEmpty();
    });
});
