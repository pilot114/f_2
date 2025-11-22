<?php

declare(strict_types=1);

namespace App\System\Factory;

use Symfony\Component\HttpFoundation\Request;

class RequestFactory
{
    public function create(): Request
    {
        return Request::createFromGlobals();
    }
}
