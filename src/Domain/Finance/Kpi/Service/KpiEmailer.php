<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\Service;

use DateTimeImmutable;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class KpiEmailer
{
    public const DEFAULT_FROM = 'bot@sibvaleo.com';

    public function __construct(
        private MailerInterface $mailer,
    ) {
    }

    /**
     * @param list<string> $emails
     * @param list<UploadedFile> $files
     */
    public function send(
        array  $emails,
        array  $files,
        string $departmentName,
        string $empName,
    ): void {
        $html = "<p><b>
            Добрый день! Вам был отправлен файл с данными для расчета премий КПИ. Воспользуйтесь обработкой для загрузки данных в ЗУП.
            Подробную инструкцию можете 
            <u><a href='https://docs.siberianhealth.com/spaces/SDRND/pages/157359958/KPI+%D0%9F%D0%BE%D0%BB%D1%8C%D0%B7%D0%BE%D0%B2%D0%B0%D1%82%D0%B5%D0%BB%D1%8C%D1%81%D0%BA%D0%B0%D1%8F+%D0%B8%D0%BD%D1%81%D1%82%D1%80%D1%83%D0%BA%D1%86%D0%B8%D1%8F+%E2%84%963+%D0%B4%D0%BB%D1%8F+%D1%80%D0%B0%D1%81%D1%87%D0%B5%D1%82%D1%87%D0%B8%D0%BA%D0%BE%D0%B2+%D0%97%D0%9F+1%D0%A1'>
                посмотреть по ссылке
            </a></u>
        </b></p>";

        $now = (new DateTimeImmutable())->format('d.m.Y, h:i');

        $email = (new Email())
            ->from(self::DEFAULT_FROM)
            ->to(...$emails)
            ->cc('kpi_bot@sibvaleo.com')
            ->subject("$departmentName, $empName, $now")
            ->html($html);

        foreach ($files as $file) {
            $email->attachFromPath($file->getPathname(), $file->getClientOriginalName(), 'application/vnd.ms-excel');
        }

        $this->mailer->send($email);
    }
}
