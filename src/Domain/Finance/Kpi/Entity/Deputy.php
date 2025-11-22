<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\Entity;

use Database\ORM\Attribute\Column;
use Database\ORM\Attribute\Entity;
use DateTimeImmutable;

#[Entity('tehno.kpi_deputy', sequenceName: 'tehno.KPI_DEPUTY_sq')]
class Deputy
{
    public function __construct(
        #[Column]
        public int $id,
        #[Column(name: 'user_id')]
        private int $currentUserId,
        #[Column(name: 'deputy_user_id')]
        private DeputyUser $deputyUser,
        #[Column(name: 'start_date')]
        private DateTimeImmutable $dateStart,
        #[Column(name: 'end_date')]
        private DateTimeImmutable $dateEnd,
    ) {
    }

    public function update(DateTimeImmutable $startDate, DateTimeImmutable $endDate, DeputyUser $deputyUser): void
    {
        $this->dateStart = $startDate;
        $this->dateEnd = $endDate;
        $this->deputyUser = $deputyUser;
    }

    public function toArray(): array
    {
        return [
            'id'         => $this->id,
            'dateStart'  => $this->dateStart,
            'dateEnd'    => $this->dateEnd,
            'deputyUser' => $this->deputyUser->toArray(),
        ];
    }
}
