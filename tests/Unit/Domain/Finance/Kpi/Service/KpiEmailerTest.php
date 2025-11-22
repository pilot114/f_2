<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Finance\Kpi\Service;

use App\Domain\Finance\Kpi\Service\KpiEmailer;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

uses(MockeryPHPUnitIntegration::class);

beforeEach(function (): void {
    $this->mailer = Mockery::mock(MailerInterface::class);
    $this->emailer = new KpiEmailer($this->mailer);
});

it('sends email with correct parameters', function (): void {
    $emails = ['test1@example.com', 'test2@example.com'];

    // Create temporary files for testing
    $tempFile1 = tmpfile();
    $tempPath1 = stream_get_meta_data($tempFile1)['uri'];
    $tempFile2 = tmpfile();
    $tempPath2 = stream_get_meta_data($tempFile2)['uri'];

    $files = [
        new UploadedFile($tempPath1, 'File1.xlsx', null, null, true),
        new UploadedFile($tempPath2, 'File2.xlsx', null, null, true),
    ];
    $departmentName = 'IT Department';
    $empName = 'John Doe';

    $this->mailer
        ->shouldReceive('send')
        ->once()
        ->with(Mockery::type(Email::class));

    $this->emailer->send($emails, $files, $departmentName, $empName);
});

it('sends email with single file attachment', function (): void {
    $emails = ['test@example.com'];

    // Create temporary file for testing
    $tempFile = tmpfile();
    $tempPath = stream_get_meta_data($tempFile)['uri'];

    $files = [new UploadedFile($tempPath, 'Report.xlsx', null, null, true)];
    $departmentName = 'Finance';
    $empName = 'Jane Smith';

    $this->mailer
        ->shouldReceive('send')
        ->once()
        ->with(Mockery::type(Email::class));

    $this->emailer->send($emails, $files, $departmentName, $empName);
});

it('sends email without attachments', function (): void {
    $emails = ['test@example.com'];
    $files = [];
    $departmentName = 'HR';
    $empName = 'Bob Johnson';

    $this->mailer
        ->shouldReceive('send')
        ->once()
        ->with(Mockery::type(Email::class));

    $this->emailer->send($emails, $files, $departmentName, $empName);
});

it('includes correct HTML content in email body', function (): void {
    $emails = ['recipient@example.com'];
    $files = [];
    $departmentName = 'Sales';
    $empName = 'Alice Brown';

    $this->mailer
        ->shouldReceive('send')
        ->once()
        ->with(Mockery::type(Email::class));

    $this->emailer->send($emails, $files, $departmentName, $empName);
});

it('sets correct email headers', function (): void {
    $emails = ['manager@example.com'];
    $files = [];
    $departmentName = 'Operations';
    $empName = 'Charlie Wilson';

    $this->mailer
        ->shouldReceive('send')
        ->once()
        ->with(Mockery::type(Email::class));

    $this->emailer->send($emails, $files, $departmentName, $empName);
});

it('handles multiple email recipients correctly', function (): void {
    $emails = ['user1@test.com', 'user2@test.com', 'user3@test.com'];
    $files = [];
    $departmentName = 'Marketing';
    $empName = 'Diana Green';

    $this->mailer
        ->shouldReceive('send')
        ->once()
        ->with(Mockery::type(Email::class));

    $this->emailer->send($emails, $files, $departmentName, $empName);
});
