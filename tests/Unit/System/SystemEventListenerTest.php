<?php

declare(strict_types=1);

namespace App\Tests\Unit\System;

use App\Common\Service\File\TempFileRegistry;
use App\System\SystemEventListener;
use Exception;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleErrorEvent;
use Symfony\Component\Console\Exception\RuntimeException as ConsoleRuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;

uses(MockeryPHPUnitIntegration::class);

beforeEach(function (): void {
    $this->tempFileRegistry = Mockery::mock(TempFileRegistry::class);
    $this->systemEventListener = new SystemEventListener('test', $this->tempFileRegistry);
});

// Тесты для метода alwaysOkStatus
it('устанавливает HTTP статус 200 для любого ответа', function (): void {
    // Создаем реальный ответ
    $response = new Response('', 400);

    // Создаем мок HttpKernelInterface
    $kernel = Mockery::mock(HttpKernelInterface::class);

    // Создаем запрос
    $request = new Request();

    // Создаем реальное событие ResponseEvent
    $event = new ResponseEvent(
        $kernel,
        $request,
        HttpKernelInterface::MAIN_REQUEST,
        $response
    );

    // Вызываем тестируемый метод
    $this->systemEventListener->alwaysOkStatus($event);

    // Проверяем, что статус ответа изменился на 200
    expect($event->getResponse()->getStatusCode())->toBe(200);
});

// Тесты для метода handleCliError
it('пропускает обработку для ConsoleRuntimeException', function (): void {
    // Создаем моки ввода и вывода
    $input = Mockery::mock(InputInterface::class);
    $output = Mockery::mock(OutputInterface::class);

    // Создаем реальное исключение ConsoleRuntimeException
    $exception = new ConsoleRuntimeException('Test runtime exception');

    // Создаем реальное событие
    $event = new ConsoleErrorEvent($input, $output, $exception, null);

    // Перенаправляем вывод в буфер
    ob_start();
    $this->systemEventListener->handleCliError($event);
    $output = ob_get_clean();

    // Проверяем, что вывод пустой (ничего не вывелось)
    expect($output)->toBe('');
});

it('обрабатывает ошибки консоли для специфичных исключений', function (): void {
    // Создаем мок команды
    $command = Mockery::mock(Command::class);
    $command->shouldReceive('getName')
        ->andReturn('test:command');

    // Создаем моки ввода и вывода
    $input = Mockery::mock(InputInterface::class);
    $output = Mockery::mock(OutputInterface::class);

    // Создаем реальное исключение
    $exception = new Exception('Тестовая ошибка', 0);

    // Устанавливаем исключение для теста
    $event = new ConsoleErrorEvent($input, $output, $exception, $command);

    // Перенаправляем вывод в буфер
    ob_start();
    $this->systemEventListener->handleCliError($event);
    $output = ob_get_clean();

    // Проверяем, что вывод содержит ожидаемую информацию
    expect($output)->toContain('ERROR')
        ->toContain('test:command')
        ->toContain('Тестовая ошибка');
});

// Тесты для метода handleException
it('создает JSON-ответ с кодом ошибки из HttpException', function (): void {
    // Создаем мок HttpKernelInterface
    $kernel = Mockery::mock(HttpKernelInterface::class);

    // Создаем запрос с RPC данными
    $request = new Request([], [], [], [], [], [], json_encode([
        'id'      => 123,
        'jsonrpc' => '2.0',
        'method'  => 'test',
    ]));

    // Создаем реальное HttpException
    $exception = new HttpException(404, 'HTTP ошибка');

    // Создаем реальное событие
    $event = new ExceptionEvent(
        $kernel,
        $request,
        HttpKernelInterface::MAIN_REQUEST,
        $exception
    );

    // Вызываем тестируемый метод
    $this->systemEventListener->handleException($event);

    // Проверяем ответ
    $response = $event->getResponse();
    expect($response)->toBeInstanceOf(JsonResponse::class);

    $content = json_decode($response->getContent(), true);
    expect($content['jsonrpc'])->toBe('2.0')
        ->and($content['error']['code'])->toBe(404)
        ->and($content['error']['message'])->toBe('HTTP ошибка')
        ->and($content['id'])->toBe(123);
});

it('использует код из обычного исключения, если HttpException не предоставляет код', function (): void {
    // Создаем мок HttpKernelInterface
    $kernel = Mockery::mock(HttpKernelInterface::class);

    // Создаем запрос с RPC данными
    $request = new Request([], [], [], [], [], [], json_encode([
        'id'      => 456,
        'jsonrpc' => '2.0',
        'method'  => 'test',
    ]));

    // Создаем обычное исключение с кодом
    $exception = new \RuntimeException('Обычная ошибка', 42);

    // Создаем реальное событие
    $event = new ExceptionEvent(
        $kernel,
        $request,
        HttpKernelInterface::MAIN_REQUEST,
        $exception
    );

    // Вызываем тестируемый метод
    $this->systemEventListener->handleException($event);

    // Проверяем ответ
    $response = $event->getResponse();
    expect($response)->toBeInstanceOf(JsonResponse::class);

    $content = json_decode($response->getContent(), true);
    expect($content['jsonrpc'])->toBe('2.0')
        ->and($content['error']['code'])->toBe(42)
        ->and($content['error']['message'])->toBe('Обычная ошибка')
        ->and($content['id'])->toBe(456);
});

it('ищет первый код ошибки в цепочке исключений, если текущее имеет код 0', function (): void {
    // Создаем мок HttpKernelInterface
    $kernel = Mockery::mock(HttpKernelInterface::class);

    // Создаем запрос с RPC данными
    $request = new Request([], [], [], [], [], [], json_encode([
        'id'      => 789,
        'jsonrpc' => '2.0',
        'method'  => 'test',
    ]));

    // Создаем вложенное исключение с кодом
    $previous = new \RuntimeException('Предыдущая ошибка', 555);

    // Создаем основное исключение с кодом 0 и предыдущим исключением
    $exception = new Exception('Основная ошибка', 0, $previous);

    // Создаем реальное событие
    $event = new ExceptionEvent(
        $kernel,
        $request,
        HttpKernelInterface::MAIN_REQUEST,
        $exception
    );

    // Вызываем тестируемый метод
    $this->systemEventListener->handleException($event);

    // Проверяем ответ
    $response = $event->getResponse();
    expect($response)->toBeInstanceOf(JsonResponse::class);

    $content = json_decode($response->getContent(), true);
    expect($content['jsonrpc'])->toBe('2.0')
        ->and($content['error']['code'])->toBe(555)
        // Проверяем, что сообщение берется из предыдущего исключения
        ->and($content['error']['message'])->toBe('Предыдущая ошибка')
        ->and($content['id'])->toBe(789);
});

it('обрабатывает запросы без id в JSON-RPC', function (): void {
    // Создаем мок HttpKernelInterface
    $kernel = Mockery::mock(HttpKernelInterface::class);

    // Создаем запрос без ID
    $request = new Request([], [], [], [], [], [], json_encode([
        'jsonrpc' => '2.0',
        'method'  => 'test',
    ]));

    // Создаем исключение
    $exception = new \RuntimeException('Ошибка без ID', 100);

    // Создаем реальное событие
    $event = new ExceptionEvent(
        $kernel,
        $request,
        HttpKernelInterface::MAIN_REQUEST,
        $exception
    );

    // Вызываем тестируемый метод
    $this->systemEventListener->handleException($event);

    // Проверяем ответ
    $response = $event->getResponse();
    expect($response)->toBeInstanceOf(JsonResponse::class);

    $content = json_decode($response->getContent(), true);
    expect($content['jsonrpc'])->toBe('2.0')
        ->and($content['error']['code'])->toBe(100)
        ->and($content['error']['message'])->toBe('Ошибка без ID')
        ->and($content['id'])->toBeNull();
});

// Тесты для метода handleTerminate
it('очищает временные файлы при завершении', function (): void {
    // Проверяем, что метод clear вызывается один раз
    $this->tempFileRegistry->expects('clear')
        ->once();

    $this->systemEventListener->handleTerminate();
});
