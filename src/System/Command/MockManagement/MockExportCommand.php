<?php

declare(strict_types=1);

namespace App\System\Command\MockManagement;

use PSX\OpenRPC\Method;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'system:mock:export', description: 'Экспортировать мок-ответы в файл')]
class MockExportCommand extends AbstractMockCommand
{
    protected function configure(): void
    {
        $this
            ->addArgument('file', InputArgument::REQUIRED, 'Путь к файлу для сохранения мок-ответов в формате JSON')
            ->setHelp('Команда экспортирует все настроенные мок-ответы в указанный JSON-файл.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        /** @var string $file */
        $file = $input->getArgument('file');

        $openRPC = $this->mockService->build();

        $exportData = [
            'openrpc' => $openRPC->getOpenrpc(),
            'methods' => [],
        ];

        /** @var array<Method> $openRpcMethods */
        $openRpcMethods = $openRPC->getMethods() ?? [];

        foreach ($openRpcMethods as $method) {

            $methodData = [
                'name' => $method->getName(),
            ];

            if ($method->getSummary()) {
                $methodData['summary'] = $method->getSummary();
            }

            if ($method->getExamples()) {
                $methodData['examples'] = [];
                foreach ($method->getExamples() as $example) {
                    $exampleData = [
                        'name' => $example->getName(),
                    ];

                    if ($example->getDescription()) {
                        $exampleData['description'] = $example->getDescription();
                    }

                    if ($example->getParams()) {
                        $exampleData['params'] = [];
                        foreach ($example->getParams() as $param) {
                            $exampleData['params'][] = [
                                'name'  => $param->getName(),
                                'value' => $this->cleanValue($param->getValue()),
                            ];
                        }
                    }

                    if ($example->getResult()) {
                        $exampleData['result'] = [
                            'name'  => $example->getResult()->getName(),
                            'value' => $this->cleanValue($example->getResult()->getValue()),
                        ];
                    }

                    $methodData['examples'][] = $exampleData;
                }
            }

            $exportData['methods'][] = $methodData;
        }

        $content = json_encode($exportData,
            JSON_PRETTY_PRINT |
            JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES |
            JSON_PRESERVE_ZERO_FRACTION
        );

        if (!is_dir(dirname($file))) {
            $io->error('Не удалось записать файл: ' . $file);
            return Command::FAILURE;
        }

        if (file_put_contents($file, $content)) {
            $io->success('Спецификация моков экспортирована в файл: ' . $file);
            return Command::SUCCESS;
        }

        $io->error('Не удалось записать файл: ' . $file);
        return Command::FAILURE;
    }

    private function cleanValue(mixed $value): mixed
    {
        if (is_string($value)) {
            $value = str_replace('\r\n', "\n", $value);
            $value = str_replace('\n', "\n", $value);
            $value = str_replace('\r', "\n", $value);
        } elseif (is_array($value)) {
            foreach ($value as $k => $v) {
                $value[$k] = $this->cleanValue($v);
            }
        } elseif (is_object($value)) {
            $value = $this->cleanValue((array) $value);
        }

        return $value;
    }
}
