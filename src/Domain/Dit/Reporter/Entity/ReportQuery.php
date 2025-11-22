<?php

declare(strict_types=1);

namespace App\Domain\Dit\Reporter\Entity;

class ReportQuery
{
    /**
     * @var ReportField[]
     */
    public array $fields;
    /**
     * @var ReportParam[]
     */
    public array $params;
    /**
     * @var ReportQuery[]
     */
    public array $sub;

    public function __construct(
        public string $sql = '',
        public string $caption = '',
        public string $keyField = '',
        public string $masterField = '',
        public string $detailField = '',
        array $fields = [],
        array $params = [],
        array $sub = [],
    ) {
        $fields = array_filter($fields, static fn ($value): bool => is_array($value));
        $params = array_filter($params, static fn ($value): bool => is_array($value));
        $sub = array_filter($sub, static fn ($value): bool => is_array($value));

        $this->fields = array_map(static fn ($x): ReportField => new ReportField(...$x), $fields);
        $this->params = array_map(static fn ($x): ReportParam => new ReportParam(...$x), $params);
        $this->sub = array_map(static fn ($x): ReportQuery => new ReportQuery(...$x), $sub);
    }

    public function toArray(): array
    {
        usort($this->params, fn (ReportParam $a, ReportParam $b): int => $b->required <=> $a->required);
        $this->params = array_filter($this->params, fn (ReportParam $x): bool => $x->name !== 'cur');
        $this->params = array_values($this->params);

        return [
            'keyField'    => $this->keyField,
            'masterField' => $this->masterField,
            'detailField' => $this->detailField,
            'fields'      => array_map(static fn ($x): array => (array) $x, $this->fields),
            'params'      => array_map(static fn ($x): array => (array) $x, $this->params),
            'sub'         => array_map(static fn ($x): array => (array) $x, $this->sub),
        ];
    }
}
