<?php

declare(strict_types=1);

use App\System\SentryUserResolver;
use Sentry\Event;
use Sentry\EventHint;
use Symfony\Bundle\SecurityBundle\Security;

beforeEach(function (): void {
    $this->security = mock(Security::class);
    $this->resolver = new SentryUserResolver($this->security);
    $this->event = Event::createEvent();
    $this->hint = new EventHint([]);
});

it('sets user data from security user', function (): void {
    $user = createSecurityUser(123, 'test_user');

    $this->security
        ->shouldReceive('getUser')
        ->once()
        ->andReturn($user);

    $result = ($this->resolver)($this->event, $this->hint);

    expect($result)->toBe($this->event);
    $userData = $result->getUser();
    expect($userData->getId())->toBe('123');
    expect($userData->getUsername())->toBe('test_user');
});

it('handles null user', function (): void {
    $this->security
        ->shouldReceive('getUser')
        ->once()
        ->andReturn(null);

    $result = ($this->resolver)($this->event, $this->hint);

    expect($result)->toBe($this->event);
    $userData = $result->getUser();
    expect($userData->getId())->toBeNull();
    expect($userData->getUsername())->toBeNull();
});

it('works without hint parameter', function (): void {
    $user = createSecurityUser(456, 'another_user', 'another@example.com');

    $this->security
        ->shouldReceive('getUser')
        ->once()
        ->andReturn($user);

    $result = ($this->resolver)($this->event, null);

    expect($result)->toBe($this->event);
    $userData = $result->getUser();
    expect($userData->getId())->toBe('456');
    expect($userData->getUsername())->toBe('another_user');
});
