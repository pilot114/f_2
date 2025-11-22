<?php

declare(strict_types=1);

namespace App\Common\DTO;

/**
 * Если Enum имплементирует Titleable - GetTitle() автоматически используется
 * для отображения в апи человеко-читаемого названия
 */
interface Titleable
{
    public function getTitle(): string;
}
