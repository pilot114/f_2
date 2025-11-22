<?php

declare(strict_types=1);

namespace App\Domain\Dit\Gifts\Entity;

use Database\ORM\Attribute\Column;
use Database\ORM\Attribute\Entity;
use DateTimeImmutable;

#[Entity('tehno.sertificat_use')]
class CertificateUsage
{
    public const AUTO_WRITE_OFF = 'Автосписание';
    private bool $isCanceled = false;
    private bool $isInitialSumAddition = false;

    public function __construct(
        #[Column] public readonly int $id,
        #[Column(name: 'sertificat_number')] public readonly string $certificateNumber,
        #[Column(name: 'summ')] public readonly float $value,
        #[Column(name: 'sum_remains')] public readonly float $sumRemains,
        #[Column(name: 'header_id')] private int $headerId,
        #[Column(name: 'header')] public readonly string $headerName,
        #[Column] public readonly ?string $commentary = null,
        #[Column(name: 'summ_use_date')] public readonly ?DateTimeImmutable $date = null,
    ) {
    }

    public function isCanceled(): bool
    {
        return $this->isCanceled;
    }

    public function markAsCanceled(): void
    {
        $this->isCanceled = true;
    }

    public function isCancelOperation(): bool
    {
        return $this->headerName === self::AUTO_WRITE_OFF && $this->value > 0;
    }

    public function isAutoWriteOff(): bool
    {
        return $this->headerName === self::AUTO_WRITE_OFF && $this->value < 0;
    }

    public function isRealWriteOff(): bool
    {
        return $this->headerName !== self::AUTO_WRITE_OFF && $this->value < 0;
    }

    public function getHeaderId(): int
    {
        return $this->headerId;
    }

    public function setHeaderId(int $headerId): void
    {
        $this->headerId = $headerId;
    }

    public function isInitialSumAddition(): bool
    {
        return $this->commentary
            && (str_contains($this->commentary, 'Массовая загрузка на старте проекта')
                || str_contains(strtolower($this->commentary), 'первичное начисление'));
    }

    public function toArray(): array
    {
        return [
            'id'                   => $this->id,
            'certificateNumber'    => $this->certificateNumber,
            'value'                => $this->value,
            'sumRemains'           => $this->sumRemains,
            'headerId'             => $this->headerId,
            'headerName'           => $this->headerName,
            'date'                 => $this->date?->format(DateTimeImmutable::ATOM),
            'commentary'           => $this->commentary,
            'isCanceled'           => $this->isCanceled,
            'isInitialSumAddition' => $this->isInitialSumAddition,
        ];
    }

    public function markAsInitialSumAddition(): void
    {
        $this->isInitialSumAddition = true;
    }
}
