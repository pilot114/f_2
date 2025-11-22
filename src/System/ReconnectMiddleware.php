<?php

declare(strict_types=1);

namespace App\System;

use Database\Connection\CpConnection;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

/**
 * Обрывает соединение с БД перед обработкой каждого сообщения в очереди.
 * Решает проблемы
 * ORA-03113: end-of-file on communication channel
 * ORA-03114: not connected to ORACLE
 * в worker процессах.
 */
class ReconnectMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly CpConnection $conn,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $this->conn->getOriginalConnection()->close();

        $this->logger->notice('Successfully reconnected to database');

        return $stack->next()->handle($envelope, $stack);
    }
}
