<?php

declare(strict_types=1);

use App\Domain\Partners\SaleStructure\Exception\PartnerDomainException;

it('creates exception with code, message and context', function (): void {
    $this->dataset = [
        'dataset' => [
            'id' => 1,
        ],
    ];
    $this->e = new PartnerDomainException("Test 404 message", 404, $this->dataset);
    expect($this->e)->toBeInstanceOf(PartnerDomainException::class)
        ->getCode()->toBe(404)
        ->getMessage()->toBe('Test 404 message')
        ->getContext()->toBe($this->dataset);
});

it('throws and catches', function (): void {
    try {
        throw new PartnerDomainException('Empty message');
    } catch (PartnerDomainException $e) {
        expect($e)->toBeInstanceOf(PartnerDomainException::class)
            ->getMessage()->toBe('Empty message');
    }
});
