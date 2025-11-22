<?php

declare(strict_types=1);

namespace App\Tests\Unit\CallCenter\UseDesk\Entity;

use App\Domain\CallCenter\UseDesk\Entity\Client;

it('can create client with id and name', function (Client $client): void {
    expect($client->id)->toBe(12345)
        ->and($client->name)->toBe('Иван Иванов');
})->with('usedesk_client');
