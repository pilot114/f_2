<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\Entity;

use App\Common\Helper\EnumerableWithTotal;
use App\Common\Service\Integration\StaticClient;
use App\Domain\Finance\Kpi\Enum\KpiType;
use App\Domain\Portal\Files\Enum\ImageSize;
use Database\ORM\Attribute\Column;
use Database\ORM\Attribute\Entity;
use Generator;

#[Entity('test.cp_emp')]
class KpiEmployee
{
    public function __construct(
        #[Column] private int $id,
        #[Column] private string $name,
        #[Column(name: 'has_userpic')] private bool $hasUserpic = false,
        /** @var array<int, Kpi> */
        #[Column(collectionOf: Kpi::class)] private array $kpi = [],
        /** @var array<int, KpiDepartmentState> */
        #[Column(collectionOf: KpiDepartmentState::class)] private array $positions = [],
    ) {
    }

    /**
     * @return Generator<Kpi>
     */
    public function getKpi(): Generator
    {
        foreach ($this->kpi as $kpi) {
            yield $kpi;
        }
    }

    public function toArray(): array
    {
        return [
            'id'                             => $this->id,
            'name'                           => $this->name,
            'countPastEmptyKpi'              => $this->countPastEmptyKpi(),
            'countPastIsFilledAndNotSentKpi' => $this->countPastIsFilledAndNotSentKpi(),
        ];
    }

    /** @return array{userpicSmall: ?string, userpicMedium: ?string} */
    public function getUserPics(): array
    {
        return [
            'userpicSmall'  => $this->hasUserpic ? StaticClient::getUserpicByUserId($this->id, ImageSize::SMALL) : null,
            'userpicMedium' => $this->hasUserpic ? StaticClient::getUserpicByUserId($this->id, ImageSize::MEDIUM) : null,
        ];
    }

    /** @return array{kpiMonthly: ?array, kpiBimonthly: ?array, kpiQuarterly: ?array} */
    public function getActualKpiEachType(): array
    {
        $actual = EnumerableWithTotal::build($this->kpi)->filter(fn (Kpi $x): bool => $x->isActualPeriod());
        $getKpiByType = fn (KpiType $type): ?Kpi => $actual->first(fn (Kpi $x): bool => $x->getType() === $type);

        return [
            'kpiMonthly'   => $getKpiByType(KpiType::MONTHLY)?->toArray($this->id),
            'kpiBimonthly' => $getKpiByType(KpiType::BIMONTHLY)?->toArray($this->id),
            'kpiQuarterly' => $getKpiByType(KpiType::QUARTERLY)?->toArray($this->id),
        ];
    }

    public function countActualEmptyKpi(): int
    {
        return EnumerableWithTotal::build($this->kpi)
            ->filter(static fn (Kpi $x): bool => $x->isActualPeriod() && $x->isEmpty())
            ->getTotal();
    }

    /** @return array{positionName: string, isBoss: bool} */
    public function getMainPosition(): array
    {
        $main = $this->positions[array_key_first($this->positions)];
        return [
            'positionName' => $main->getName(),
            'isBoss'       => $main->isBoss(),
        ];
    }

    private function countPastEmptyKpi(): int
    {
        return EnumerableWithTotal::build($this->kpi)
            ->filter(static fn (Kpi $x): bool => !$x->isActualPeriod() && $x->isEmpty())
            ->getTotal();
    }

    private function countPastIsFilledAndNotSentKpi(): int
    {
        return EnumerableWithTotal::build($this->kpi)
            ->filter(static fn (Kpi $x): bool => !$x->isActualPeriod() && $x->isFilled() && !$x->isSent())
            ->getTotal();
    }
}
