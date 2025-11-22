<?php

declare(strict_types=1);

namespace App\Domain\Portal\Security\Controller;

use App\Common\Attribute\RpcMethod;
use App\Common\Attribute\RpcParam;
use App\Common\Exception\InvariantDomainException;
use App\Domain\Dit\Reporter\UseCase\GetReportUseCase;
use App\Domain\Portal\Security\Entity\SecurityUser;
use App\Domain\Portal\Security\Repository\SecurityQueryRepository;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class RequestAccessController
{
    public function __construct(
        private SecurityQueryRepository $secRepo,
        private SecurityUser $currentUser,
        protected GetReportUseCase $getReportUseCase,
        protected MailerInterface $mailer,
    ) {
    }

    #[RpcMethod(
        'portal.security.requestAccess',
        'Запрос на выдачу прав у владельца ресурса',
    )]
    public function __invoke(
        #[RpcParam('для кого запрашивается право')]
        int $empId,
        #[RpcParam('id запрашиваемого ресурса')]
        int $resourceId,
        #[RpcParam('тип ресурса')]
        string $resourceType = 'rep_report'
    ): bool {
        $hasPermission = $this->secRepo->hasPermission($empId, $resourceType, $resourceId);
        if ($hasPermission) {
            throw new InvariantDomainException("Право на $resourceId уже выдано для пользователя $empId");
        }

        if ($resourceType !== 'rep_report') {
            throw new InvariantDomainException('Реализовано только для отчетов (rep_report)');
        }

        $targetUser = $this->secRepo->findOneBy([
            'id' => $empId,
        ]);

        if ($targetUser === null) {
            throw new InvariantDomainException("Пользователь с id $empId не найден");
        }

        $report = $this->getReportUseCase->getReport($resourceId, $targetUser);
        $owner = $report->getOwner();
        $reportName = $report->getName();

        if ($owner === null) {
            throw new InvariantDomainException('У отчёта не найден владелец');
        }

        // Ссылку на выдачу прав оставить на старом портале
        $allowUrl = 'https://cp.siberianhealth.com/acl/allowAccess?resource_type=' . $resourceType .
            '&resource_id=' . $resourceId . '&emp_id=' . $empId . '&access_type=read';
        if ($this->currentUser->id !== $empId) {
            $allowUrl .= '&emp_id_target=' . $this->currentUser->id;
        }

        // для письма
        $reportUrl = 'https://cp.siberianhealth.com/reporter2/' . $resourceId;

        $text = "Для сотрудника: " . $targetUser->name . " (" . $targetUser->email . ")";
        if ($targetUser->id !== $this->currentUser->id) {
            $text .= "Cотрудником: " . $this->currentUser->name . " (" . $this->currentUser->email . ")";
        }

        $html = "
        <p>Запрошены права на отчет <a href='$reportUrl'>$reportName</a></p>
        <p>$text</p>
        <p>
            Нажмите, чтобы дать доступ: <a href='$allowUrl'>Дать доступ!</a>
        </p>
        Спасибо!";

        $email = (new Email())
            ->from('bot@sibvaleo.com')
            ->to($owner['email'])
            ->subject("Запрос на выдачу прав для отчета № $resourceId ($reportName)")
            ->html($html);

        $this->mailer->send($email);

        return true;
    }
}
