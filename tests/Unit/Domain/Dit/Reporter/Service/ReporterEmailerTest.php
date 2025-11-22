<?php

declare(strict_types=1);

use App\Domain\Dit\Reporter\Message\ExportReportMessage;
use App\Domain\Dit\Reporter\Service\ReporterEmailer;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

beforeEach(function (): void {
    $this->mailer = Mockery::mock(MailerInterface::class);
    $this->service = new ReporterEmailer($this->mailer);
});

afterEach(function (): void {
    Mockery::close();
});

it('sends email with report attachment', function (): void {
    $file = Mockery::mock(UploadedFile::class);
    $file->shouldReceive('getPathname')
        ->andReturn('/tmp/test_report.xlsx');
    $file->shouldReceive('getClientOriginalName')
        ->andReturn('report.xlsx');

    $message = new ExportReportMessage(
        reportId: 1,
        input: [],
        userId: 123,
        userEmail: 'user@example.com'
    );

    $this->mailer
        ->shouldReceive('send')
        ->once()
        ->with(Mockery::on(function (Email $email): bool {
            $recipients = $email->getTo();
            $from = $email->getFrom();

            return count($recipients) === 1
                && $recipients[0]->getAddress() === 'user@example.com'
                && count($from) === 1
                && $from[0]->getAddress() === ReporterEmailer::DEFAULT_FROM
                && str_contains($email->getSubject(), 'Test Report')
                && str_contains($email->getHtmlBody(), 'Test Report')
                && str_contains($email->getHtmlBody(), 'Ваш отчёт готов');
        }));

    $this->service->sendReport(
        reportName: 'Test Report',
        file: $file,
        message: $message,
        executionTime: 5
    );
});

it('escapes HTML in report name', function (): void {
    $file = Mockery::mock(UploadedFile::class);
    $file->shouldReceive('getPathname')
        ->andReturn('/tmp/test_report.xlsx');
    $file->shouldReceive('getClientOriginalName')
        ->andReturn('report.xlsx');

    $message = new ExportReportMessage(
        reportId: 1,
        input: [],
        userId: 123,
        userEmail: 'user@example.com'
    );

    $this->mailer
        ->shouldReceive('send')
        ->once()
        ->with(Mockery::on(function (Email $email): bool {
            $htmlBody = $email->getHtmlBody();
            return str_contains($htmlBody, '&lt;script&gt;')
                && !str_contains($htmlBody, '<script>');
        }));

    $this->service->sendReport(
        reportName: '<script>alert("xss")</script>',
        file: $file,
        message: $message,
        executionTime: 5
    );
});

it('uses correct email template', function (): void {
    $file = Mockery::mock(UploadedFile::class);
    $file->shouldReceive('getPathname')
        ->andReturn('/tmp/test_report.xlsx');
    $file->shouldReceive('getClientOriginalName')
        ->andReturn('report.xlsx');

    $message = new ExportReportMessage(
        reportId: 1,
        input: [],
        userId: 123,
        userEmail: 'user@example.com'
    );

    $this->mailer
        ->shouldReceive('send')
        ->once()
        ->with(Mockery::on(function (Email $email): bool {
            $htmlBody = $email->getHtmlBody();
            return str_contains($htmlBody, '<p><b>')
                && str_contains($htmlBody, 'Добрый день!')
                && str_contains($htmlBody, 'Отчёт:');
        }));

    $this->service->sendReport(
        reportName: 'Monthly Report',
        file: $file,
        message: $message,
        executionTime: 5
    );
});

it('attaches file with correct mime type', function (): void {
    $file = Mockery::mock(UploadedFile::class);
    $file->shouldReceive('getPathname')
        ->andReturn('/tmp/test_report.xlsx');
    $file->shouldReceive('getClientOriginalName')
        ->andReturn('report.xlsx');

    $message = new ExportReportMessage(
        reportId: 1,
        input: [],
        userId: 123,
        userEmail: 'user@example.com'
    );

    $this->mailer
        ->shouldReceive('send')
        ->once()
        ->with(Mockery::on(function (Email $email): bool {
            $attachments = $email->getAttachments();
            return count($attachments) === 1
                && $attachments[0]->getContentType() === 'application/vnd.ms-excel';
        }));

    $this->service->sendReport(
        reportName: 'Test Report',
        file: $file,
        message: $message,
        executionTime: 5
    );
});
