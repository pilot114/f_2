<?php

declare(strict_types=1);

namespace App\Tests\Unit\CallCenter\UseDesk\Entity;

use App\Domain\CallCenter\UseDesk\Entity\MarkedChat;

it('can create marked chat with all properties', function (MarkedChat $markedChat): void {
    expect($markedChat->getId())->toBe(1)
        ->and($markedChat->chatId)->toBe(12345)
        ->and($markedChat->markUserId)->toBe(9999);
})->with('usedesk_marked_chat');
