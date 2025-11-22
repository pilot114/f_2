<?php

declare(strict_types=1);

namespace App\Domain\Portal\Excel\Factory;

use App\Common\Service\Excel\ExcelExporterInterface;
use InvalidArgumentException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ExcelExporterFactory
{
    /**
     * @param ExcelExporterInterface[] $exporters
     */
    public function __construct(
        private readonly iterable $exporters
    ) {
        $this->validateUniqueExporterNames();
    }

    public function create(string $exporterName): ExcelExporterInterface
    {
        foreach ($this->exporters as $exporter) {
            if ($exporter->getExporterName() === $exporterName) {
                return $exporter;
            }
        }

        throw new NotFoundHttpException("Экспортер '$exporterName' не найден");
    }

    private function validateUniqueExporterNames(): void
    {
        $exporterNames = [];

        foreach ($this->exporters as $exporter) {
            $name = $exporter->getExporterName();
            $className = $exporter::class;

            if (isset($exporterNames[$name])) {
                throw new InvalidArgumentException("Дублирующееся имя экспортера: '$name'. Используется в классах: {$exporterNames[$name]} и $className");
            }

            $exporterNames[$name] = $className;
        }
    }
}
