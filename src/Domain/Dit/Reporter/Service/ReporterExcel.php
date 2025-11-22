<?php

declare(strict_types=1);

namespace App\Domain\Dit\Reporter\Service;

use App\Common\Service\Excel\BaseCommandExcelService;
use App\Domain\Dit\Reporter\Entity\ReportField;
use App\Domain\Dit\Reporter\Message\ExportReportMessage;

class ReporterExcel extends BaseCommandExcelService
{
    public function setName(string $reportName, ?ExportReportMessage $message = null): self
    {
        $this->fileName = $reportName;
        if ($message instanceof ExportReportMessage && $message->input !== []) {
            $reportParams = implode('_', $message->input);
            $this->fileName = "$reportName $reportParams";
        }
        return $this;
    }

    /**
     * @param iterable<array<string, mixed>> $rows
     * @param ReportField[] $fields
     * @return $this
     */
    public function setContent(iterable $rows, array $fields): self
    {
        $map = [];
        foreach ($fields as $field) {
            $map[$field->fieldName] = $field->displayLabel;
        }

        $cb = fn (array $item): array => array_combine(
            array_map(fn ($k) => $map[$k], array_keys($item)),
            $item
        );

        $this
            ->setTabs(['reporter экспорт'])
            ->eachItem($cb, $rows)
            ->setDefaultConfig()
        ;
        return $this;
    }
}
