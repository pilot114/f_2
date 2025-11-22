<?php

declare(strict_types=1);

namespace App\Domain\Portal\Cabinet\DTO;

use App\Common\Attribute\RpcParam;
use DateTimeImmutable;
use Symfony\Component\Validator\Constraints as Assert;

readonly class AddMessageToColleaguesRequest
{
    /**
     * @param list<int> $notifyUserIds
     */
    public function __construct(
        #[RpcParam('Дата начала показа сообщения')]
        public DateTimeImmutable $startDate,
        #[RpcParam('Дата конца показа сообщения')]
        public DateTimeImmutable $endDate,
        #[RpcParam('Текст сообщения')]
        #[Assert\Length(max: 3000)]
        public string $message,
        #[RpcParam('Список ID пользователей для уведомления')]
        #[Assert\All([
            new Assert\Type('integer'),
        ])]
        /** @var int[] */
        public array $notifyUserIds = [],
    ) {
    }
}
