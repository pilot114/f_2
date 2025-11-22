<?php

declare(strict_types=1);

namespace App\Domain\Marketing\CustomerHistory\UseCase;

use App\Domain\Marketing\CustomerHistory\DTO\EditCustomerHistoryRequest;
use App\Domain\Marketing\CustomerHistory\Repository\CustomerHistoryCommandRepository;
use App\Domain\Portal\Security\Entity\SecurityUser;
use DomainException;
use Throwable;

readonly class EditCustomerHistoryUseCase
{
    public function __construct(
        private CustomerHistoryCommandRepository $repository,
        private SecurityUser $securityUser,
    ) {
    }

    public function editCustomerHistory(
        EditCustomerHistoryRequest $customerHistoryRequest,
    ): true {
        try {
            $this->repository->editStoryOfCustomer(
                id: $customerHistoryRequest->id,
                userId: $this->securityUser->id,
                status: $customerHistoryRequest->status->value,
                preview: $customerHistoryRequest->preview,
                text: $customerHistoryRequest->text,
                commentary: $customerHistoryRequest->commentary,
                shops: $customerHistoryRequest->getShopsString(),
            );
        } catch (Throwable $e) {
            throw new DomainException("Ошибка сохранения истории клиента: " . $e->getMessage(), 400, $e);
        }

        return true;
    }
}
