<?php

declare(strict_types=1);

namespace App\Domain\CallCenter\UseDesk\Enum;

enum UserType: string
{
    /** В сервисе UseDesk у сообщения может быть два типа отправителя:
     * client - это человек, который задаёт вопрос в чат
     * user - это сотрудник нашей компании, который отвечает на вопрос клиента.
     * trigger - автоматическое сообщение от системы.
     */
    case CLIENT = 'client';
    case EMPLOYEE = 'user';
    case TRIGGER = 'trigger';
}
