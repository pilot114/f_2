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

#[AsCommand(name: 'system:mock:remove', description: 'Удалить мок-ответ для API метода')]
class MockRemoveCommand extends AbstractMockCommand
{
    protected function configure(): void
    {
        $this
            ->addArgument('method', InputArgument::REQUIRED, 'Имя метода JSON-RPC')
            ->addArgument('params', InputArgument::OPTIONAL, 'JSON-строка с параметрами для метода (опционально)')
            ->setHelp(<<<EOT
Команда для удаления мок-ответа для API метода.

Примеры использования:
  <info>system:mock:remove method.name</info> - Удалить все моки для метода
  <info>system:mock:remove method.name '{"id":1,"name":"test"}'</info> - Удалить мок для метода с конкретными параметрами
EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $method = $input->getArgument('method');
        $paramsStr = $input->getArgument('params');

        if (!is_string($method)) {
            $io->error('Имя метода должно быть строкой');
            return Command::FAILURE;
        }

        $params = null;
        if ($paramsStr) {
            if (!is_string($paramsStr)) {
                $io->error('Параметры должны быть строкой JSON');
                return Command::FAILURE;
            }

            $params = json_decode($paramsStr, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $io->error('Неверный формат JSON для параметров: ' . json_last_error_msg());
                return Command::FAILURE;
            }

            // Ensure params is an array
            if (!is_array($params)) {
                $io->error('Параметры должны быть массивом');
                return Command::FAILURE;
            }
        }

        try {
            $this->mockService->removeMock($method, $params);
        } catch (Throwable $e) {
            $io->error('Не удалось удалить мок: ' . $e->getMessage());
            return Command::FAILURE;
        }

        if ($params) {
            $io->success('Мок для метода ' . $method . ' с параметрами удален');
        } else {
            $io->success('Все моки для метода ' . $method . ' удалены');
        }
        return Command::SUCCESS;

    }
}
