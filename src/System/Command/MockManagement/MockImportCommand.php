<?php

declare(strict_types=1);

namespace App\System\Command\MockManagement;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

#[AsCommand(name: 'system:mock:import', description: 'Импортировать мок-ответы из файла')]
class MockImportCommand extends AbstractMockCommand
{
    protected function configure(): void
    {
        $this
            ->addArgument('file', InputArgument::REQUIRED, 'Путь к файлу с мок-ответами в формате JSON')
            ->setHelp('Команда импортирует мок-ответы из указанного JSON-файла.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $file = $input->getArgument('file');

        // Ensure the file path is a string
        if (!is_string($file)) {
            $io->error('Путь к файлу должен быть строкой');
            return Command::FAILURE;
        }

        $fs = new Filesystem();
        if (!$fs->exists($file)) {
            $io->error('Файл не найден: ' . $file);
            return Command::FAILURE;
        }

        $content = $fs->readFile($file);

        $data = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $io->error('Не удалось разобрать JSON: ' . json_last_error_msg());
            return Command::FAILURE;
        }

        if (!is_array($data)) {
            $io->error('Файл не содержит корректную структуру данных');
            return Command::FAILURE;
        }

        $mockSpec = json_encode($data);
        if ($mockSpec === false) {
            $io->error('Не удалось сериализовать данные обратно в JSON');
            return Command::FAILURE;
        }

        $this->mockService->setMockJsonSpec($mockSpec);

        if ($this->mockService->saveMockSpec($this->mockService->build())) {
            $io->success('Мок-ответы успешно импортированы из файла: ' . $file);
            return Command::SUCCESS;
        }

        $io->error('Не удалось сохранить импортированные мок-ответы');
        return Command::FAILURE;
    }
}
