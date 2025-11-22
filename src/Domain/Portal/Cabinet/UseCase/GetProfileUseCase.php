<?php

declare(strict_types=1);

namespace App\Domain\Portal\Cabinet\UseCase;

use App\Domain\Portal\Cabinet\Entity\Profile;
use App\Domain\Portal\Cabinet\Repository\ProfileQueryRepository;

class GetProfileUseCase
{
    public function __construct(
        private ProfileQueryRepository $readProfile,
    ) {
    }

    public function getProfile(int $userId): Profile
    {
        return $this->readProfile->getProfileByUserId($userId);
    }
}
