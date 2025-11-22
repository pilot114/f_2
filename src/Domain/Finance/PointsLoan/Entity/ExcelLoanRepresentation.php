<?php

declare(strict_types=1);

namespace App\Domain\Finance\PointsLoan\Entity;

use App\Common\Helper\CountableFormatter;
use Database\ORM\Attribute\Column;
use Database\ORM\Attribute\Entity;

#[Entity(name: 'net.employee_point_credits')]
readonly class ExcelLoanRepresentation
{
    public function __construct(
        #[Column] public int $id,
        #[Column(name: 'employee_contract')] public string $contract,
        #[Column(name: 'employee_name')] public string $name,
        #[Column(name: 'employee_country')] public string $country,
        #[Column] public int $months,
        #[Column(name: 'month_payment')] public float $monthlyPayment,
        #[Column] public float $summ,
        #[Column(name: 'guarantor_name')] public ?string $guarantorName = null,
        #[Column(name: 'guarantor_contract')] public ?string $guarantorContract = null,
    ) {
    }

    public function getColumnsData(): array
    {
        return [
            $this->contract,
            $this->name,
            $this->country,
            $this->makeComment(),
            $this->summ,
        ];
    }

    public static function getHeaders(): array
    {
        return [
            'Контракт',
            'ФИО',
            'Страна',
            'Примечание',
            'Баллы',
        ];
    }

    private function makeComment(): string
    {
        $template = $this->months && $this->monthlyPayment
            ? "На " . CountableFormatter::pluralize($this->months, ['месяц', 'месяца', 'месяцев']) . " по {$this->monthlyPayment} баллов"
            : '-';
        if ($this->guarantorName && $this->guarantorContract) {
            $template .= ", гарант ({$this->guarantorContract}) {$this->guarantorName}";
        }

        return $template;
    }
}
