<?php

declare(strict_types=1);

namespace App\Domain\Finance\PointsLoan\Entity;

use Database\ORM\Attribute\Column;
use Database\ORM\Attribute\Entity;
use DateTimeImmutable;

#[Entity('net.employee')]
class Partner
{
    public array $emails = [];

    public function __construct(
        #[Column(name: 'id')] public readonly int                                          $id,
        #[Column(name: 'contract')] public readonly string                                 $contract,
        #[Column(name: 'name')] public readonly string                                     $name,
        #[Column(name: 'country')] public readonly Country                                 $country,
        #[Column(name: 'd_start')] public readonly DateTimeImmutable                       $startDate,
        #[Column(name: 'd_end')] public readonly ?DateTimeImmutable                        $closedAt = null,
        #[Column(name: 'partner_stat', collectionOf: PartnerStats::class)] protected array $stats = [],
        #[Column(name: 'violation')] public readonly ?Violation                            $violation = null,
    ) {
    }

    public function isActive(): bool
    {
        return !($this->closedAt instanceof DateTimeImmutable);
    }

    public function getMonthsInCompany(DateTimeImmutable $currentDate = new DateTimeImmutable()): int
    {
        $endDate = $this->closedAt ?? $currentDate;

        $startYear = (int) $this->startDate->format('Y');
        $startMonth = (int) $this->startDate->format('m');
        $endYear = (int) $endDate->format('Y');
        $endMonth = (int) $endDate->format('m');
        $months = ($endYear - $startYear) * 12 + ($endMonth - $startMonth) + 1;

        // Если мы завершили сотрудничество с партнёром, то этот месяц не считаем.
        if ($this->closedAt instanceof DateTimeImmutable) {
            $months--;
        }

        return max(1, $months);
    }

    public function toArray(): array
    {
        return [
            'id'             => $this->id,
            'contract'       => $this->contract,
            'name'           => $this->name,
            'email'          => $this->getEmailsAsString(),
            'country'        => $this->country->toArray(),
            'monthInCompany' => $this->getMonthsInCompany(),
            'violation'      => $this->violation?->toArray(),
            'isActive'       => $this->isActive(),
            'stats'          => array_map(function (PartnerStats $stat): array {
                static $orderNumber = 1;
                return [
                    ...[
                        'N' => $orderNumber++,
                    ],
                    ...$stat->toArray(),
                ];
            }, array_values($this->stats)),
        ];
    }

    public function getEmailsAsString(): string
    {
        return implode(', ', $this->emails);
    }

    public function addEmails(array $emails): void
    {
        $emails = array_filter($emails, fn ($email): bool => !is_null($email) && filter_var($email, FILTER_VALIDATE_EMAIL));

        $this->emails = array_unique([...$this->emails, ...$emails]);
    }
}
