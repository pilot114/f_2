<?php

declare(strict_types=1);

namespace App\System\Command\MockManagement;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'system:mock:list', description: 'Показать список всех мок-ответов')]
class MockListCommand extends AbstractMockCommand
{
    protected function configure(): void
    {
        $this->setHelp('Команда выводит список всех настроенных мок-ответов для API методов.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $openRPC = $this->mockService->build();
        $methods = $openRPC->getMethods();

        if ($methods === null || $methods === []) {
            $io->info('Моки не найдены');
            return Command::SUCCESS;
        }

        $rows = [];
        foreach ($methods as $method) {
            $methodName = $method->getName();
            $examples = $method->getExamples();

            if (empty($examples)) {
                continue;
            }

            foreach ($examples as $example) {
                $params = $example->getParams();
                $result = $example->getResult();

                $paramValues = [];
                if ($params) {
                    foreach ($params as $param) {
                        $paramValues[$param->getName()] = $param->getValue();
                    }
                }

                $rows[] = [
                    $methodName,
                    $example->getName(),
                    $paramValues === [] ? '-' : json_encode($paramValues, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
                    $result ? json_encode($result->getValue(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) : '-',
                ];
            }
        }

        if ($rows === []) {
            $io->info('Моки не найдены');
            return Command::SUCCESS;
        }

        $io->table(['Метод', 'Пример', 'Параметры', 'Ответ'], $rows);
        return Command::SUCCESS;
    }
}
