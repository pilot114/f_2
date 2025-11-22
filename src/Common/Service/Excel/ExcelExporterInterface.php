<?php

declare(strict_types=1);

namespace App\Common\Service\Excel;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('excel.exporter')]
interface ExcelExporterInterface
{
    /**
     * Возвращает уникальное имя экспортера
     */
    public function getExporterName(): string;

    /**
     * Возвращает имя файла для скачивания
     */
    public function getFileName(): string;

    /**
     * Экспортирует данные в Excel и стримит в браузер
     *
     * @param array $params Параметры для экспорта
     */
    public function export(array $params): void;
}
