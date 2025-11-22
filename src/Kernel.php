<?php

declare(strict_types=1);

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public function __construct(
        protected string $environment,
        protected bool $debug
    ) {
        date_default_timezone_set('Asia/Novosibirsk');
        parent::__construct($environment, $debug);
    }
}
