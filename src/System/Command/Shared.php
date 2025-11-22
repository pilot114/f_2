<?php

declare(strict_types=1);

namespace App\System\Command;

class Shared
{
    public string $currentFileName;
    public array $sqlCache;
    public string $currentKey;
    public string $sqlForReplace;
}
