<?php

declare(strict_types=1);

namespace App\Domain\Dit\Reporter\Command;

use App\Domain\Dit\Reporter\Repository\ReportQueryRepository;
use App\Domain\Dit\Reporter\UseCase\ExecuteReportUseCase;
use App\Domain\Portal\Security\Entity\SecurityUser;
use App\Domain\Portal\Security\Repository\SecurityQueryRepository;
use Database\Connection\CpConnection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

#[AsCommand(name: 'reporter:debug', description: 'анализ отчётов репортера')]
class DebugReporter extends Command
{
    private SecurityUser $currentUser;
    private array $lastExecution;

    public function __construct(
        private CpConnection $conn,
        private ReportQueryRepository $repo,
        private SecurityQueryRepository $secRepo,
        private ExecuteReportUseCase $executeReportUseCase,
    ) {
        parent::__construct();

        $this->currentUser = $this->secRepo->findOrFail(4026, 'не найден сотрудник');
    }

    protected function configure(): void
    {
        $this
            ->addArgument('id', mode: InputArgument::OPTIONAL)
            ->addOption('stats', mode: InputOption::VALUE_NONE)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->conn->procedure('acl.pacl.authorize_from_cp', [
            'login' => $this->currentUser->login,
        ]);

        /** @var string $id */
        $id = $input->getArgument('id');
        if ($id !== '') {
            $this->printReport((int) $id, $output);
            return Command::SUCCESS;
        }

        $reports = $this->repo->getReportListFlat();

        if ($input->getOption('stats')) {
            $this->printReportStats($reports, $output);
            return Command::SUCCESS;
        }

        ini_set('memory_limit', -1);

        foreach ($reports as $report) {
            if ($report['report_type'] === '1') {
                continue;
            }
            $id = (int) $report['id'];

            $longExecute = [
                10762, // 10мин, 1млн записей
            ];
            if (in_array($id, $longExecute, true)) {
                continue;
            }

            $withErrors = [
                11800,
                13301,
                12124,
            ];
            if (in_array($id, $withErrors, true)) {
                continue;
            }

            $this->debugReport($id, $output);
        }
        return Command::SUCCESS;
    }

    protected function debugReport(int $id, OutputInterface $output): void
    {
        $query = $this->printReport($id, $output);
        if ($query === []) {
            $output->writeln("<error>Отчёт $id. Нет данных</error>");
            return;
        }

        $paramsForExecute = [];
        foreach ($query['params'] as $param) {
            if ($param['required']) {
                $paramsForExecute[$param['name']] = $param['defaultValue'];
            }
        }
        $output->writeln('Выполнение с параметрами: ' . json_encode($paramsForExecute));

        try {
            $result = $this->measurePerformance(
                fn (): array => $this->executeReportUseCase->executeReport($id, $this->currentUser, $paramsForExecute),
            );
            $message = sprintf("Отчёт %s. Кол-во: %s (%s)", $id, $result[1], json_encode($this->lastExecution));
            $output->writeln($message);
            //            file_put_contents('./debug_reporter.php', "$message\n", FILE_APPEND);
        } catch (Throwable $e) {
            $message = "Отчёт $id. Ошибка: {$e->getMessage()}";
            $output->writeln("<error>$message</error>");
            //            file_put_contents('./debug_reporter.php', "$message\n", FILE_APPEND);
        }
    }

    protected function measurePerformance(callable $callback): array
    {
        $memoryBefore = memory_get_usage();
        $timeBefore = time();

        $result = $callback();

        $timeAfter = time();
        $memoryAfter = memory_get_usage();

        $executionTime = $timeAfter - $timeBefore;
        $memoryUsed = $memoryAfter - $memoryBefore;

        $this->lastExecution = [
            'sec'    => $executionTime,
            'memory' => $this->formatBytes($memoryUsed),
        ];

        return $result;
    }

    private function formatBytes(int $size): string
    {
        $units = ['b', 'kb', 'mb', 'gb'];
        for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }
        return round($size, 2) . ' ' . $units[$i];
    }

    protected function printReport(int $id, OutputInterface $output): array
    {
        $report = $this->repo->getReport($id);
        $data = $report->toArray();

        $output->writeln(sprintf('%s (%s)', $data['name'], $data['owner']['name'] ?? 'no owner name'));

        $query = $data['data']['queries'][0] ?? [];

        if ($query === []) {
            return [];
        }

        $table = (new Table($output))
            ->setHeaderTitle('Поля')
            ->setHeaders(['fieldName', 'bandName', 'displayLabel', 'isCurrency'])
            ->setStyle('box')
        ;
        foreach ($query['fields'] as $field) {
            $table->addRow($field);
        }
        $table->render();

        $table = (new Table($output))
            ->setHeaderTitle('Параметры')
            ->setHeaders(['name', 'caption', 'dataType', 'defaultValue', 'dictionaryId', 'customValues', 'required'])
            ->setStyle('box')
        ;
        foreach ($query['params'] as $field) {
            $table->addRow($field);
        }
        $table->render();

        return $query;
    }

    protected function printReportStats(array $reports, OutputInterface $output): void
    {
        // 1 - папка с отчётами, 2 - сам отчёт
        $column = array_column($reports, 'report_type');
        $reportsCount = array_count_values(array_filter($column))['2'];

        $column = array_column($reports, 'owner');
        $countByOwners = array_count_values(array_filter($column));

        $owners = $this->repo->getReportOwners(array_keys($countByOwners));
        $owners = iterator_to_array($owners);

        $table = new Table($output);

        $table->setHeaderTitle(sprintf("Отчётов: %s", $reportsCount));
        $table->setHeaders(['Сотрудник', 'Сколько отчётов'])->setStyle('box');

        usort($owners, function (mixed $a, mixed $b) use ($countByOwners): int {
            if (!is_array($a) || !is_array($b)) {
                return 0;
            }
            return $countByOwners[$b['id']] <=> $countByOwners[$a['id']];
        });

        foreach ($owners as $owner) {
            if (!is_array($owner)) {
                continue;
            }
            $info = sprintf("(%s) %s", $owner['id'] ?? '', $owner['name'] ?? '');
            $info = ($owner['active'] ?? '') === 'Y' ? $info : "<error>$info (неактивен)</error>";
            $table->addRow([$info, $countByOwners[$owner['id'] ?? 0] ?? 0]);
        }
        $table->render();
    }
}
