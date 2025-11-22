<?php

declare(strict_types=1);

namespace App\Tests\Unit\User\Cabinet\Service;

use App\Domain\Portal\Cabinet\Entity\MessageToColleagues;
use App\Domain\Portal\Cabinet\Entity\User;
use App\Domain\Portal\Cabinet\Service\ColleagueEmailer;
use DateTimeImmutable;
use Mockery;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

beforeEach(function (): void {
    $this->mailer = Mockery::mock(MailerInterface::class);
    $this->htmlSanitizer = Mockery::mock(HtmlSanitizerInterface::class);
    $this->emailer = new ColleagueEmailer($this->mailer, $this->htmlSanitizer);
});

afterEach(function (): void {
    Mockery::close();
});

it('sends email with correct content and recipients', function (): void {
    $user = new User(9999, 'Иванов Иван', 'ivanov@test.com');
    $message = new MessageToColleagues(
        1,
        $user,
        'Буду в отпуске с понедельника',
        new DateTimeImmutable('2025-01-01'),
        new DateTimeImmutable('2025-01-05'),
        new DateTimeImmutable()
    );

    $emails = ['petrov@test.com', 'sidorov@test.com'];
    $senderName = 'Иванов Иван';
    $this->htmlSanitizer->shouldReceive('sanitize')->once();
    $this->mailer->shouldReceive('send')
        ->once()
        ->with(Mockery::type(Email::class));

    $this->emailer->send($emails, $message, $senderName);
});

it('sanitizes html content in message', function (): void {
    $user = new User(9999, 'Иванов Иван', 'ivanov@test.com');
    $message = new MessageToColleagues(
        1,
        $user,
        'Сообщение с <script>alert("xss")</script> и переносом строки' . "\n" . 'вторая строка',
        new DateTimeImmutable('2025-01-01'),
        new DateTimeImmutable('2025-01-05'),
        new DateTimeImmutable()
    );

    $emails = ['test@test.com'];
    $senderName = 'Иванов Иван';
    $this->htmlSanitizer->shouldReceive('sanitize')->once();

    $this->mailer->shouldReceive('send')
        ->once()
        ->with(Mockery::type(Email::class));

    $this->emailer->send($emails, $message, $senderName);
});

it('sends to multiple recipients', function (): void {
    $user = new User(9999, 'Иванов Иван', 'ivanov@test.com');
    $message = new MessageToColleagues(
        1,
        $user,
        'Тестовое сообщение',
        new DateTimeImmutable('2025-01-01'),
        new DateTimeImmutable('2025-01-05'),
        new DateTimeImmutable()
    );

    $emails = ['user1@test.com', 'user2@test.com', 'user3@test.com'];
    $senderName = 'Иванов Иван';
    $this->htmlSanitizer->shouldReceive('sanitize')->once();

    $this->mailer->shouldReceive('send')
        ->once()
        ->with(Mockery::type(Email::class));

    $this->emailer->send($emails, $message, $senderName);
});

it('handles empty recipients list by not sending email', function (): void {
    $user = new User(9999, 'Иванов Иван', 'ivanov@test.com');
    $message = new MessageToColleagues(
        1,
        $user,
        'Тестовое сообщение',
        new DateTimeImmutable('2025-01-01'),
        new DateTimeImmutable('2025-01-05'),
        new DateTimeImmutable()
    );

    $emails = [];
    $senderName = 'Иванов Иван';

    $this->mailer->shouldNotReceive('send');

    $this->emailer->send($emails, $message, $senderName);
});
