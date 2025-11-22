<?php

declare(strict_types=1);

namespace App\Tests\Unit\User\Cabinet\Entity;

use App\Domain\Portal\Cabinet\Entity\Contacts;

beforeEach(function (): void {
    $this->entity = new Contacts(
        email: 'test@ema.il',
        telegram: '@test',
        phone: '1111'
    );
});

it('create', function (): void {
    expect($this->entity)->toBeInstanceOf(Contacts::class);
});

it('to array', function (): void {
    expect($this->entity->toArray())
        ->toBeArray()
        ->toBe([
            'email'    => 'test@ema.il',
            'telegram' => '@test',
            'phone'    => '1111',
        ]);
});

it('email getter', function (): void {
    expect($this->entity->getEmail())->toBe('test@ema.il');
});

it('telegram getter', function (): void {
    expect($this->entity->getEmail())->toBe('test@ema.il');
});

it('telegram setter', function (): void {
    $this->entity->setTelegram('1111');
    expect($this->entity->getTelegram())->toBe('1111');
});

it('phone getter', function (): void {
    expect($this->entity->getPhone())->toBe('1111');
});

it('phone setter', function (): void {
    $this->entity->setPhone('2222');
    expect($this->entity->getPhone())->toBe('2222');
});
