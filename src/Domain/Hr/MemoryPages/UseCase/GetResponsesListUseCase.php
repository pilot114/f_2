<?php

declare(strict_types=1);

namespace App\Domain\Hr\MemoryPages\UseCase;

use App\Domain\Hr\MemoryPages\Entity\Response;
use App\Domain\Hr\MemoryPages\Repository\ResponsesQueryRepository;
use Illuminate\Support\Enumerable;

class GetResponsesListUseCase
{
    public function __construct(
        private ResponsesQueryRepository $repository
    ) {
    }

    /** @return Enumerable<int, Response> */
    public function getList(): Enumerable
    {
        return $this->repository->findAll();
    }
}
