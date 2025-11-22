<?php

declare(strict_types=1);

namespace App\System\Generate;

use Generator;

interface ScenarioInterface
{
    /**
     * Логика получения данных, на основе которых генерится код
     */
    public function load(): void;

    /**
     * Генератор вида ИмяКласса => СодержимоеКласса
     *
     * @return Generator<string, string>
     */
    public function run(string $outputNamespace): Generator;
}
