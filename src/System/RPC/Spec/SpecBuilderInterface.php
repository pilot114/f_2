<?php

declare(strict_types=1);

namespace App\System\RPC\Spec;

interface SpecBuilderInterface
{
    public function build(): mixed;
}
