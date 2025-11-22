<?php

declare(strict_types=1);

namespace App\Domain\Portal\Excel\Exporter;

use App\Common\Service\Excel\ExcelExporterInterface;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Writer;

abstract class AbstractExporter implements ExcelExporterInterface
{
    public function __construct(
        protected readonly Writer $writer,
    ) {
        $this->setBasicOptions();
    }

    /**
     * Создаёт writer c базовыми настройками
     */
    protected function setBasicOptions(): void
    {
        $options = $this->writer->getOptions();
        // Автоматически создавать новый лист если превышено число строк в текущем листе (1,048,576) по дефолту - true
        $options->SHOULD_CREATE_NEW_SHEETS_AUTOMATICALLY = true;
        // Дефолтная высота строк (в пунктах)
        $options->DEFAULT_ROW_HEIGHT = 16;

        //Можно задать дефолтные стили для строк и переписать их позже
        $options->DEFAULT_ROW_STYLE = $this->makeBasicStyle();
    }

    protected function makeBasicStyle(): Style
    {
        $defaultRowStyle = new Style();
        $defaultRowStyle->setFontName('Calibri');
        $defaultRowStyle->setFontSize(12);

        return $defaultRowStyle;
    }

    protected function sanitizeFileName(string $fileName): string
    {
        return preg_replace('/[^\w\-_\.]/', '_', $fileName) ?? $fileName;
    }
}
