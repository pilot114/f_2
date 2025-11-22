<?php

declare(strict_types=1);

namespace App\System;

use App\Common\Service\File\TempFileRegistry;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleErrorEvent;
use Symfony\Component\Console\Exception\RuntimeException as ConsoleRuntimeException;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelEvents;

class SystemEventListener
{
    public function __construct(
        protected string $env,
        protected TempFileRegistry $tempFileRegistry
    ) {
    }

    // в RPC не используем HTTP статус (чтобы использовать batch запросы)
    #[AsEventListener(KernelEvents::RESPONSE)]
    public function alwaysOkStatus(ResponseEvent $event): void
    {
        $event->setResponse(
            $event->getResponse()->setStatusCode(200)
        );
    }

    #[AsEventListener(ConsoleEvents::ERROR)]
    public function handleCliError(ConsoleErrorEvent $event): void
    {
        // свои exception symfony обрабатывает правильно
        if ($event->getError()::class === ConsoleRuntimeException::class) {
            return;
        }

        $commandName = $event->getCommand()?->getName() ?? 'UNKNOWN COMMAND';
        $message = $event->getError()->getMessage();
        $file = $event->getError()->getFile();
        $line = $event->getError()->getLine();
        echo "$file:$line\n";
        echo "\033[31mERROR\033[0m in $commandName: $message\n\n";
        // compact trace
        foreach ($event->getError()->getTrace() as $i => $item) {
            if (!isset($item['file'], $item['line'])) {
                continue;
            }
            if (str_contains($item['file'], '/vendor/')) {
                continue;
            }
            echo sprintf("#%s %s:%s\n", $i, $item['file'], $item['line']);
        }
    }

    #[AsEventListener(KernelEvents::EXCEPTION)]
    public function handleException(ExceptionEvent $event): void
    {
        $nextException = $exception = $event->getThrowable();

        do {
            $message = $nextException->getMessage();
            if ($nextException instanceof HttpException) {
                $realCode = $nextException->getStatusCode();
            } else {
                $realCode = $nextException->getCode();
            }
            if ($realCode) {
                break;
            }
        } while ($nextException = $nextException->getPrevious());

        $payload = $this->extractRpcPayloadFromRequest($event->getRequest());

        $error = [
            'code'    => $realCode,
            'message' => $message,
        ];

        if ($this->env !== 'prod') {
            $error['data'] = [
                'exception' => [
                    'name'    => $exception::class,
                    'message' => $exception->getMessage(),
                    'code'    => $exception->getCode(),
                    'file'    => $exception->getFile(),
                    'line'    => $exception->getLine(),
                    'trace'   => $exception->getTraceAsString(),
                ],
            ];
        }

        $event->setResponse(new JsonResponse([
            'jsonrpc' => '2.0',
            'error'   => $error,
            'id'      => $payload['id'] ?? null,
        ]));
    }

    #[AsEventListener(KernelEvents::TERMINATE)]
    public function handleTerminate(): void
    {
        $this->tempFileRegistry->clear();
    }

    private function extractRpcPayloadFromRequest(Request $request): ?array
    {
        $content = $request->getContent();
        if (!is_string($content)) {
            return null;
        }
        $payload = json_decode($content, true);
        if (!is_array($payload)) {
            return null;
        }
        return $payload;
    }
}
