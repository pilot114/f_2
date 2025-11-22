<?php

declare(strict_types=1);

namespace App\Domain\Portal\Cabinet\UseCase;

use App\Common\Helper\EnumerableWithTotal;
use App\Domain\Portal\Cabinet\DTO\GetUsersListRequest;
use App\Domain\Portal\Cabinet\Entity\User;
use App\Domain\Portal\Cabinet\Repository\UserQueryRepository;
use Illuminate\Support\Enumerable;

class GetUsersListUseCase
{
    public function __construct(
        private UserQueryRepository $userQueryRepository,
    ) {
    }

    /** @return Enumerable<int, User> */
    public function getList(GetUsersListRequest $request): Enumerable
    {
        // Если на поиск передана пустая строка - возвращаем пустую коллекцию
        if (trim($request->search) === '') {
            return EnumerableWithTotal::build([]);
        }

        return $this->userQueryRepository->findActiveUsersWithEmail($request->search);
    }
}
