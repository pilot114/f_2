<?php

declare(strict_types=1);

namespace App\System\Command\MockManagement;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

#[AsCommand(name: 'system:mock:add', description: 'Добавить мок-ответ для API метода')]
class MockAddCommand extends AbstractMockCommand
{
    protected function configure(): void
    {
        $this
            ->addArgument('method', InputArgument::REQUIRED, 'Имя метода JSON-RPC')
            ->addArgument('response', InputArgument::REQUIRED, 'JSON-строка с мок-ответом')
            ->addArgument('params', InputArgument::OPTIONAL, 'JSON-строка с параметрами для метода (опционально)')
            ->setHelp(<<<EOT
Команда для добавления мок-ответа для API метода.

Примеры использования:
  <info>system:mock:add method.name '{"result":"success"}'</info> - Добавить дефолтный мок-ответ для метода
  <info>system:mock:add method.name '{"result":"success"}' '{"id":1,"name":"test"}'</info> - Добавить мок-ответ для метода с конкретными параметрами
EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $method = $input->getArgument('method');
        $response = $input->getArgument('response');
        $paramsStr = $input->getArgument('params');

        if (!is_string($method)) {
            $io->error('Имя метода должно быть строкой');
            return Command::FAILURE;
        }

        if (!is_string($response)) {
            $io->error('Ответ должен быть строкой JSON');
            return Command::FAILURE;
        }

        $responseData = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $io->error('Неверный формат JSON для ответа: ' . json_last_error_msg());
            return Command::FAILURE;
        }

        $params = null;
        if ($paramsStr !== null) {
            if (!is_string($paramsStr)) {
                $io->error('Параметры должны быть строкой JSON');
                return Command::FAILURE;
            }

            $params = json_decode($paramsStr, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $io->error('Неверный формат JSON для параметров: ' . json_last_error_msg());
                return Command::FAILURE;
            }

            if (!is_array($params)) {
                $io->error('Параметры должны быть массивом');
                return Command::FAILURE;
            }
        }

        try {
            $this->mockService->addMock($method, $responseData, $params);
        } catch (Throwable $e) {
            $io->error('Не удалось cохранить мок ' . $e->getMessage());
            return Command::FAILURE;
        }

        if ($params) {
            $io->success('Мок для метода ' . $method . ' с параметрами добавлен');
        } else {
            $io->success('Дефолтный мок для метода ' . $method . ' добавлен');
        }

        return Command::SUCCESS;
    }
}
