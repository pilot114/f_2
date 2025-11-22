<?php

declare(strict_types=1);

namespace App\Domain\Portal\Cabinet\UseCase;

use App\Domain\Portal\Cabinet\Entity\Congratulation;
use App\Domain\Portal\Cabinet\Repository\CongratulationsQueryRepository;
use DateTimeImmutable;
use Illuminate\Support\Enumerable;

class GetCongratulationsUseCase
{
    public function __construct(
        private CongratulationsQueryRepository $congratulationsQueryRepository
    ) {
    }

    /**
     * @return Enumerable<int, Congratulation>
     */
    public function getCongratulations(int $receiverId, ?DateTimeImmutable $startFrom = null): Enumerable
    {
        $startFrom = $startFrom ?? new DateTimeImmutable();
        return $this->congratulationsQueryRepository->findCongratulationsByReceiverId($receiverId, $startFrom);
    }
}
