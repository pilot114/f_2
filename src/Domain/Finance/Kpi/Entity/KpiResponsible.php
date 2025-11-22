<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\Entity;

use Database\ORM\Attribute\Column;
use Database\ORM\Attribute\Entity;
use DateTimeImmutable;

#[Entity('tehno.kpi_responsible', sequenceName: 'tehno.kpi_responsible_sq')]
class KpiResponsible
{
    public function __construct(
        #[Column]
        public int $id,
        #[Column(name: 'user_id')]
        protected KpiResponsibleUser $user,
        #[Column(name: 'enterprise_id')]
        protected KpiResponsibleEnterprise $enterprise,
        #[Column(name: 'change_date')]
        protected DateTimeImmutable $changeDate,
        #[Column(name: 'change_user_id')]
        protected int $changeUserId,
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function update(int $enterpriseId, int $userId, int $currentUserId): void
    {
        $this->enterprise->id = $enterpriseId;
        $this->user->id = $userId;
        $this->changeUserId = $currentUserId;
        $this->changeDate = new DateTimeImmutable();
    }

    public function toArray(): array
    {
        return [
            'id'         => $this->id,
            'user'       => $this->user->toArray(),
            'enterprise' => $this->enterprise->toArray(),
        ];
    }
}
