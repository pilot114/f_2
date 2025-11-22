<?php

declare(strict_types=1);

namespace App\Domain\Portal\Excel\Factory;

use OpenSpout\Writer\XLSX\Writer;

class WriterFactory
{
    public function create(): Writer
    {
        return new Writer();
    }
}
