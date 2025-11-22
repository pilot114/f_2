<?php

declare(strict_types=1);

use App\System\Factory\RequestFactory;
use Symfony\Component\HttpFoundation\Request;

it('creates request from globals', function (): void {
    $factory = new RequestFactory();
    $request = $factory->create();

    expect($request)->toBeInstanceOf(Request::class);
});
