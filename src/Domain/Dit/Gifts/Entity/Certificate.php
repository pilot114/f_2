<?php

declare(strict_types=1);

namespace App\Domain\Dit\Gifts\Entity;

use App\Common\Exception\InvariantDomainException;
use Database\ORM\Attribute\Column;
use Database\ORM\Attribute\Entity;
use DateTimeImmutable;
use Illuminate\Support\Enumerable;

#[Entity('tehno.sertificat')]
class Certificate
{
    public function __construct(
        #[Column(name: 'id')] public readonly int $id,
        #[Column(name: 'sertificat_number')] public readonly string $number,
        #[Column(name: 'employee_contract')] public readonly string $partnerContract,
        #[Column(name: 'sertificat_summ')] public readonly float $denomination,
        #[Column(name: 'sertificat_remains')] public readonly float $sumRemains,
        #[Column(name: 'sertificat_type')] public readonly CertificateType $certificateType,
        #[Column(name: 'sertificat_currency')] public readonly Currency $currency,
        #[Column(name: 'sertificat_header_id')] public readonly ?int $headerId = null,
        #[Column(name: 'sertificat_data_create')] public readonly ?DateTimeImmutable $created = null,
        #[Column(name: 'sertificat_data_end')] public readonly ?DateTimeImmutable $expires = null,
        /** @var CertificateUsage[] $usages */
        #[Column(collectionOf: CertificateUsage::class, name: 'sertificat_use')] protected array $usages = [],
    ) {
    }

    /**
     * @param Enumerable<int, CertificateUsage> $usages
     */
    public function setUsages(Enumerable $usages): void
    {
        $canceledIds = [];
        foreach ($usages as $key => $usage) {
            if (!$usage instanceof CertificateUsage) {
                throw new InvariantDomainException('в usages могут быть только экземпляры CertificateUsage');
            }
            if ($usage->isCancelOperation()) {
                $canceledIds[] = $key;
                $canceledOperation = $usages->filter(fn (CertificateUsage $item): bool => $item->id === $usage->id && !$item->isCancelOperation())->first();
                if ($canceledOperation instanceof CertificateUsage) {
                    $canceledOperation->markAsCanceled();
                }
            }
        }

        $usages = $usages->forget($canceledIds);
        $usages = $usages->sortByDesc('date');
        $initialUsage = $usages->filter(fn (CertificateUsage $item): bool => $item->isInitialSumAddition())->first();

        if ($initialUsage instanceof CertificateUsage) {
            $initialUsage->markAsInitialSumAddition();
            $initialUsage->setHeaderId((int) $this->headerId);
        }

        /** @var CertificateUsage[] $usagesArray */
        $usagesArray = $usages->values()->toArray();

        $this->usages = $usagesArray;
    }

    public function toArray(): array
    {
        return [
            'id'              => $this->id,
            'number'          => $this->number,
            'headerId'        => $this->headerId,
            'partnerContract' => $this->partnerContract,
            'denomination'    => $this->denomination,
            'sumRemains'      => $this->sumRemains,
            'created'         => $this->created?->format(DateTimeImmutable::ATOM),
            'expires'         => $this->expires?->format(DateTimeImmutable::ATOM),
            'type'            => $this->certificateType->toArray(),
            'currency'        => $this->currency->toArray(),
            'usages'          => array_map(fn (CertificateUsage $usage): array => $usage->toArray(), array_values($this->usages)),
        ];
    }

    public function getUsages(): array
    {
        return $this->usages;
    }

    public function isRealWriteOffOperationExists(): bool
    {
        $usages = $this->usages;

        foreach ($usages as $usage) {
            if ($usage->isRealWriteOff()) {
                return true;
            }
        }

        return false;
    }

    public function isAutoWriteOffOperationExists(): bool
    {
        $usages = $this->usages;

        foreach ($usages as $usage) {
            if ($usage->isAutoWriteOff() && !$usage->isCanceled()) {
                return true;
            }
        }

        return false;
    }

    public function isLatestUsageAutoWriteOff(): bool
    {
        $usages = $this->usages;

        $latestUsage = array_shift($usages);
        return $latestUsage instanceof CertificateUsage && $latestUsage->isAutoWriteOff() && !$latestUsage->isCanceled();
    }

    public function canBeDeleted(): bool
    {
        foreach ($this->usages as $usage) {
            if ($usage->isInitialSumAddition()) {
                continue;
            }
            return false;
        }

        return true;
    }

    public function calculateNewDenomination(float $amount): float
    {
        return $this->denomination + $amount;
    }
}
