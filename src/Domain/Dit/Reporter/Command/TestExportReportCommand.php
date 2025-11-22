<?php

declare(strict_types=1);

namespace App\Domain\Dit\Reporter\Command;

use App\Domain\Dit\Reporter\Message\ExportReportMessage;
use App\Domain\Dit\Reporter\Message\ExportReportMessageHandler;
use App\Domain\Portal\Security\Repository\SecurityQueryRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'reporter:test-export',
    description: 'Тестовая команда для отправки отчёта в очередь'
)]
class TestExportReportCommand extends Command
{
    public function __construct(
        private MessageBusInterface $bus,
        private SecurityQueryRepository $securityQueryRepository,
        private ExportReportMessageHandler $handler,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('reportId', InputArgument::REQUIRED, 'ID отчёта')
            ->addArgument('userId', InputArgument::REQUIRED, 'ID пользователя')
            ->addOption('input', 'i', InputOption::VALUE_REQUIRED, 'JSON с параметрами отчёта', '{}')
            ->addOption('sync', mode: InputOption::VALUE_NONE, description: 'Выполнить синхронно без очереди')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $reportId */
        $reportId = $input->getArgument('reportId');
        $reportId = (int) $reportId;
        /** @var string $userId */
        $userId = $input->getArgument('userId');
        $userId = (int) $userId;

        /** @var string $inputJson */
        $inputJson = $input->getOption('input');

        // Парсим JSON с параметрами
        $reportInput = (array) json_decode($inputJson, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $output->writeln("<error>Ошибка парсинга JSON: " . json_last_error_msg() . "</error>");
            return Command::FAILURE;
        }

        $user = $this->securityQueryRepository->find($userId);
        if ($user === null) {
            $output->writeln("<error>Пользователь с ID $userId не найден</error>");
            return Command::FAILURE;
        }

        $message = new ExportReportMessage(
            reportId: $reportId,
            input: $reportInput,
            userId: $userId,
            userEmail: $user->email,
        );

        if ($input->getOption('sync')) {
            $output->writeln("<info>Выполнение отчёта синхронно...</info>");
            ($this->handler)($message);
            $output->writeln("<info>Отчёт выполнен и отправлен на email!</info>");
            return Command::SUCCESS;
        }

        $this->bus->dispatch($message);

        $output->writeln("<info>Сообщение отправлено в очередь!</info>");
        $output->writeln("Report ID: $reportId");
        $output->writeln("User ID: $userId");
        $output->writeln("Email: $user->email");
        $output->writeln("Input: " . json_encode($reportInput, JSON_UNESCAPED_UNICODE));

        return Command::SUCCESS;
    }
}
