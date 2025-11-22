<?php

declare(strict_types=1);

namespace App\Domain\Dit\Reporter\Service;

use App\Domain\Dit\Reporter\Message\ExportReportMessage;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class ReporterEmailer
{
    public const DEFAULT_FROM = 'bot@sibvaleo.com';

    public function __construct(
        private MailerInterface $mailer,
    ) {
    }

    public function sendReport(
        string $reportName,
        UploadedFile $file,
        ExportReportMessage $message,
        int $executionTime,
    ): void {
        $reportName = htmlspecialchars($reportName, ENT_QUOTES, 'UTF-8');

        $params = '';
        foreach ($message->input as $key => $value) {
            $params .= "<li><b>$key</b>: $value</li>";
        }
        if ($params !== '') {
            $params = "Параметры отчёта: <ul>$params</ul>";
        }

        $html = "<p><b>
            Добрый день! Ваш отчёт готов и прикреплён к письму.
        </b></p>
        <p>Отчёт: <b>$reportName</b></p>
        $params
        <i>Время создания отчёта: $executionTime сек.</i>
        ";

        $email = (new Email())
            ->from(self::DEFAULT_FROM)
            ->to($message->userEmail)
            ->subject("Готов отчёт: $reportName")
            ->html($html);

        $email->attachFromPath($file->getPathname(), $file->getClientOriginalName(), 'application/vnd.ms-excel');

        $this->mailer->send($email);
    }
}
