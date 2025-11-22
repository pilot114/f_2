<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Finance\PointsLoan\DTO;

use App\Domain\Finance\PointsLoan\DTO\LoanResponse;
use App\Domain\Finance\PointsLoan\Enum\LoanStatus;

it('builds loan response from array', function (): void {
    $data = [
        'id'                 => 1,
        'accrualOperationId' => 100,
        'partnerId'          => 42,
        'startDate'          => '2024-01-01',
        'sum'                => 10000.50,
        'months'             => 12,
        'monthlyPayment'     => 833.38,
        'guarantorContract'  => 'GC-123',
        'endDate'            => '2024-12-31',
        'linkedLoanId'       => 5,
        'totalPaid'          => 5000.25,
        'loanStatus'         => LoanStatus::NOT_PAID,
    ];

    $response = LoanResponse::build($data);

    expect($response->id)->toBe(1)
        ->and($response->accrualOperationId)->toBe(100)
        ->and($response->partnerId)->toBe(42)
        ->and($response->startDate)->toBe('2024-01-01')
        ->and($response->sum)->toBe(10000.50)
        ->and($response->months)->toBe(12)
        ->and($response->monthlyPayment)->toBe(833.38)
        ->and($response->guarantorContract)->toBe('GC-123')
        ->and($response->endDate)->toBe('2024-12-31')
        ->and($response->linkedLoanId)->toBe(5)
        ->and($response->totalPaid)->toBe(5000.25)
        ->and($response->loanStatus)->toBe(LoanStatus::NOT_PAID);
});

it('handles null guarantor contract', function (): void {
    $data = [
        'id'                 => 1,
        'accrualOperationId' => 1,
        'partnerId'          => 1,
        'startDate'          => '2024-01-01',
        'sum'                => 1000.0,
        'months'             => 6,
        'monthlyPayment'     => 166.67,
        'guarantorContract'  => null,
        'endDate'            => null,
        'linkedLoanId'       => null,
        'totalPaid'          => 0.0,
        'loanStatus'         => LoanStatus::NOT_PAID,
    ];

    $response = LoanResponse::build($data);

    expect($response->guarantorContract)->toBeNull();
});

it('handles null end date', function (): void {
    $data = [
        'id'                 => 1,
        'accrualOperationId' => 1,
        'partnerId'          => 1,
        'startDate'          => '2024-01-01',
        'sum'                => 1000.0,
        'months'             => 6,
        'monthlyPayment'     => 166.67,
        'guarantorContract'  => null,
        'endDate'            => null,
        'linkedLoanId'       => null,
        'totalPaid'          => 0.0,
        'loanStatus'         => LoanStatus::NOT_PAID,
    ];

    $response = LoanResponse::build($data);

    expect($response->endDate)->toBeNull();
});

it('handles null linked loan id', function (): void {
    $data = [
        'id'                 => 1,
        'accrualOperationId' => 1,
        'partnerId'          => 1,
        'startDate'          => '2024-01-01',
        'sum'                => 1000.0,
        'months'             => 6,
        'monthlyPayment'     => 166.67,
        'guarantorContract'  => null,
        'endDate'            => null,
        'linkedLoanId'       => null,
        'totalPaid'          => 0.0,
        'loanStatus'         => LoanStatus::NOT_PAID,
    ];

    $response = LoanResponse::build($data);

    expect($response->linkedLoanId)->toBeNull();
});

it('handles paid loan status', function (): void {
    $data = [
        'id'                 => 1,
        'accrualOperationId' => 1,
        'partnerId'          => 1,
        'startDate'          => '2024-01-01',
        'sum'                => 1000.0,
        'months'             => 6,
        'monthlyPayment'     => 166.67,
        'guarantorContract'  => null,
        'endDate'            => '2024-06-30',
        'linkedLoanId'       => null,
        'totalPaid'          => 1000.0,
        'loanStatus'         => LoanStatus::PAID,
    ];

    $response = LoanResponse::build($data);

    expect($response->loanStatus)->toBe(LoanStatus::PAID)
        ->and($response->totalPaid)->toBe($response->sum);
});

it('handles zero total paid', function (): void {
    $data = [
        'id'                 => 1,
        'accrualOperationId' => 1,
        'partnerId'          => 1,
        'startDate'          => '2024-01-01',
        'sum'                => 1000.0,
        'months'             => 6,
        'monthlyPayment'     => 166.67,
        'guarantorContract'  => null,
        'endDate'            => null,
        'linkedLoanId'       => null,
        'totalPaid'          => 0.0,
        'loanStatus'         => LoanStatus::NOT_PAID,
    ];

    $response = LoanResponse::build($data);

    expect($response->totalPaid)->toBe(0.0);
});

it('handles large sum values', function (): void {
    $data = [
        'id'                 => 1,
        'accrualOperationId' => 1,
        'partnerId'          => 1,
        'startDate'          => '2024-01-01',
        'sum'                => 999999.99,
        'months'             => 60,
        'monthlyPayment'     => 16666.67,
        'guarantorContract'  => null,
        'endDate'            => null,
        'linkedLoanId'       => null,
        'totalPaid'          => 50000.0,
        'loanStatus'         => LoanStatus::NOT_PAID,
    ];

    $response = LoanResponse::build($data);

    expect($response->sum)->toBe(999999.99)
        ->and($response->months)->toBe(60);
});
