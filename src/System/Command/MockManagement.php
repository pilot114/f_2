<?php

declare(strict_types=1);

namespace App\System\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'system:mock', description: 'Управление мок-ответами для API методов')]
class MockManagement extends Command
{
    protected function configure(): void
    {
        $this->setHelp('Эта команда предоставляет доступ к подкомандам для управления мок-ответами API методов.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Управление мок-ответами для API методов');

        $io->section('Доступные подкоманды:');
        $io->listing([
            'system:mock:add - Добавить мок-ответ для метода',
            'system:mock:remove - Удалить мок-ответ для метода',
            'system:mock:list - Показать список всех мок-ответов',
            'system:mock:import - Импортировать мок-ответы из файла',
            'system:mock:export - Экспортировать мок-ответы в файл',
        ]);

        $io->section('Примеры использования:');
        $io->listing([
            'system:mock:add method_name \'{"result":"success"}\' \'{"param1":"value1"}\'',
            'system:mock:remove method_name \'{"param1":"value1"}\'',
            'system:mock:list',
            'system:mock:import /path/to/file.json',
            'system:mock:export /path/to/file.json',
        ]);

        return Command::SUCCESS;
    }
}
