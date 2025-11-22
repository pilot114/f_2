<?php

declare(strict_types=1);

namespace App\Domain\Portal\Cabinet\Service;

use App\Domain\Portal\Cabinet\Entity\MessageToColleagues;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class ColleagueEmailer
{
    public const DEFAULT_FROM = 'bot@sibvaleo.com';

    public function __construct(
        private MailerInterface $mailer,
        private HtmlSanitizerInterface $htmlSanitizer
    ) {
    }

    /**
     * @param list<string> $emails
     */
    public function send(
        array $emails,
        MessageToColleagues $message,
        string $senderName,
    ): void {
        if ($emails === []) {
            return;
        }

        $safeContents = $this->htmlSanitizer->sanitize($message->getMessage());
        $html = "
        <p><strong>Создано в профиле на карте офиса:</strong></p>
        <div style='border-left: 3px solid #007bff; padding-left: 15px; margin: 15px 0;'>
            " . nl2br($safeContents) . "
        </div>
        <p>Сообщение продублированно вам на почту, так как сотрудник считает эту информацию важной.</p>
        <p>Вы тоже можете оставить сообщение - просто перейдите в блок \"Сообщение коллегам\" в своем профиле на странице <a href='https://cp.siberianhealth.com/company/structure'>структуры компании</a>.</p>
        ";

        $email = (new Email())
            ->from(self::DEFAULT_FROM)
            ->to(...$emails)
            ->subject("Сообщение коллегам от сотрудника {$senderName}")
            ->html($html);

        $this->mailer->send($email);
    }
}
