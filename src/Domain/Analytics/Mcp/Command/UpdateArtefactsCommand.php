<?php

declare(strict_types=1);

namespace App\Domain\Analytics\Mcp\Command;

use App\Domain\Analytics\Mcp\Entity\Artefact;
use App\Domain\Analytics\Mcp\Enum\ArtefactType;
use App\Domain\Analytics\Mcp\Retriever\CacheArtefactRetriever;
use App\Domain\Analytics\Mcp\Retriever\OracleArtefactRetriever;
use Database\ORM\Attribute\Loader;
use Database\ORM\CommandRepositoryInterface;
use Database\ORM\EntityTracker;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Scheduler\Attribute\AsCronTask;
use Throwable;

#[AsCommand(name: 'analytics:updateArtefact', description: 'обновляет данные артефактов в таблице test.cp_artefact')]
#[AsCronTask(expression: '#daily', arguments: '--diff')]
class UpdateArtefactsCommand extends Command
{
    public function __construct(
        private OracleArtefactRetriever    $oracleRetriever,
        private CacheArtefactRetriever     $cacheRetriever,
        /** @var CommandRepositoryInterface<Artefact> */
        private CommandRepositoryInterface $commandRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('diff')
            ->addOption('stats')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($input->getOption('diff')) {
            $this->updateByDiff($output);
            return Command::SUCCESS;
        }
        if ($input->getOption('stats')) {
            $this->printStats($output);
            return Command::SUCCESS;
        }

        $this->createArtefacts($output, ArtefactType::TABLE);
        $this->createArtefacts($output, ArtefactType::PROCEDURE);
        $this->createArtefacts($output, ArtefactType::VIEW);

        return Command::SUCCESS;
    }

    protected function updateByDiff(OutputInterface $output): void
    {
        $diffData = $this->oracleRetriever->getDiffForLastDays(10);
        $map = [];
        foreach ($diffData as $item) {
            $map[$item['object_type']][] = mb_strtolower(sprintf('%s.%s', $item['owner'], $item['object_name']));
        }

        $table = new Table($output);
        $table->setHeaders(['ArtefactType', 'count changes'])->setStyle('box');
        foreach ($map as $type => $items) {
            $table->addRow([$type, count($items)]);
        }
        $table->render();

        foreach ($map as $type => $items) {
            $artefactType = ArtefactType::from($type);
            $artefacts = $this->oracleRetriever->getChunk($items, $artefactType);

            foreach ($artefacts as $artefactName => $artefact) {
                $artefactName = mb_strtolower($artefactName);
                $cached = $this->cacheRetriever->get($artefactName, $artefactType);

                if (!$cached instanceof Artefact) {
                    $output->writeln("INSERT $artefactName");
                    $this->commandRepository->create(new Artefact(
                        id: Loader::ID_FOR_INSERT,
                        name: $artefactName,
                        type: $artefactType,
                        content: serialize($artefact)
                    ));
                } else {
                    $output->writeln("UPDATE $artefactName");
                    $cached->setContent(serialize($artefact));
                    $this->commandRepository->update($cached);
                }
            }
        }
    }

    protected function printStats(OutputInterface $output): void
    {
        $table = new Table($output);
        $table->setHeaders(['ArtefactType', 'Oracle', 'Cache'])->setStyle('box');

        $cases = [
            ArtefactType::TABLE,
            ArtefactType::PROCEDURE,
            ArtefactType::VIEW,
        ];
        foreach ($cases as $case) {
            $table->addRow([
                $case->name,
                count($this->oracleRetriever->getNameList($case)),
                count($this->cacheRetriever->getNameList($case)),
            ]);
        }
        $table->render();
    }

    /**
     * Добавляет артефакты указанного типа
     */
    protected function createArtefacts(OutputInterface $output, ArtefactType $artefactType): void
    {
        $allArtefacts = $this->oracleRetriever->getNameList($artefactType);

        $progressBar = new ProgressBar($output, count($allArtefacts));
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%');
        $progressBar->start();

        $chunkSize = 100;
        foreach (array_chunk($allArtefacts, $chunkSize) as $chunk) {
            try {
                $artefacts = $this->oracleRetriever->getChunk($chunk, $artefactType);
            } catch (Throwable $e) {
                $output->writeln($e->getFile() . ':' . $e->getLine() . ':' . $e->getMessage());
                $progressBar->advance(count($chunk));
                continue;
            }

            foreach ($artefacts as $artefactName => $artefact) {
                $entity = new Artefact(
                    id: Loader::ID_FOR_INSERT,
                    name: mb_strtolower($artefactName),
                    type: $artefactType,
                    content: serialize($artefact)
                );

                try {
                    $this->commandRepository->create($entity);
                } catch (Throwable $e) {
                    $output->writeln($e->getFile() . ':' . $e->getLine() . ':' . $e->getMessage());
                    $output->writeln("$artefactName");
                    continue;
                }
            }
            $progressBar->advance(count($chunk));

            // memory free
            EntityTracker::clear();
        }

        $progressBar->finish();
    }
}
