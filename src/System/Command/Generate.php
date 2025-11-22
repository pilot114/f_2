<?php

declare(strict_types=1);

namespace App\System\Command;

use App\System\DomainSourceCodeFinder;
use App\System\Exception\GenerateException;
use App\System\Generate\Scenario\GenerateCommandRepository;
use App\System\Generate\Scenario\GenerateController;
use App\System\Generate\Scenario\GenerateDTO;
use App\System\Generate\Scenario\GenerateEntity;
use App\System\Generate\Scenario\GenerateProcedure;
use App\System\Generate\Scenario\GenerateQueryRepository;
use App\System\Generate\Scenario\GenerateUseCase;
use App\System\RPC\Attribute\RpcMethodLoader;
use Database\Schema\EntityRetriever;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

#[AsCommand(name: 'system:make', description: 'Генерация кода в интерактивном режиме')]
class Generate extends Command
{
    private QuestionHelper $question;
    private array $domains;
    private OutputInterface $output;

    private string $domain;
    private string $subdomain;

    public function __construct(
        private readonly EntityRetriever $retriever,
        private RpcMethodLoader          $rpcLoader,
        private DomainSourceCodeFinder   $finder,
        private string                   $projectDir,
    ) {
        parent::__construct();
        $this->question = new QuestionHelper();

        // заполняем по директориям
        foreach ($this->finder->getDomainDirs() as $dir) {
            [$domain, $subdomain] = $dir;
            $this->domains[lcfirst($domain)][lcfirst($subdomain)] = [];
        }

        // заполняем по rpc методам
        foreach ($this->rpcLoader->load() as $rpc) {
            [$domain, $subdomain, $method] = explode('.', $rpc->name);
            $domain = lcfirst($domain);
            $subdomain = lcfirst($subdomain);
            if (isset($this->domains[$domain][$subdomain])) {
                $this->domains[$domain][$subdomain][] = $method;
            } else {
                throw new GenerateException("Не найдено директории домена, соответствующей методу {$rpc->name}");
            }
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->output = $output;

        $domainName = $this->askForDomainName($input);
        if (!$domainName) {
            return Command::FAILURE;
        }
        [$this->domain, $this->subdomain] = explode('.', $domainName);
        $this->domain = lcfirst($this->domain);
        $this->subdomain = lcfirst($this->subdomain);

        // если домен новый, предлагаем создать "скелет"
        if (!isset($this->domains[$this->domain][$this->subdomain])) {
            $this->buildSkeleton($input, $this->domain, $this->subdomain);
        }

        while (true) {
            $question = new ChoiceQuestion(
                "<fg=yellow>$domainName</> Выбери сценарий",
                [
                    'Entity'          => 'Создать сущность на основе таблицы из БД',
                    'QueryRepository' => 'Класс с набором SQL запросов, логически связанных с одним Entity',
                    'ProcedureCall'   => 'Добавить вызов процедуры в репозиторий',
                    'DTO'             => 'Создать иммутабельный набор классов для API',
                    'CRUD'            => 'Стандартный CRUD для простых случаев',
                    'exit'            => 'Выйти',
                ],
            );
            /** @var string $scenarioName */
            $scenarioName = $this->question->ask($input, $this->output, $question);
            if ($scenarioName === 'exit') {
                break;
            }
            try {
                $this->handleScenario($input, $scenarioName);
            } catch (Exception $e) {
                /** @var array{file: string, line: string} $item */
                foreach ($e->getTrace() as $item) {
                    $output->writeln('<error>' . $item['file'] . ':' . $item['line'] . ':</error>');
                }
                $output->writeln('<error>' . $e->getMessage() . '</error>');
            }
        }

        return Command::SUCCESS;
    }

    // TODO: автокомплит на таблицы, сущности и пр.
    private function handleScenario(InputInterface $input, string $scenarioName): void
    {
        if ($scenarioName === 'ProcedureCall') {
            $this->generateProcedureCall(
                $this->askForProcedureNames($input),
                $this->askForRepoName($input),
            );
            return;
        }

        $tableNames = $this->askForTableNames($input);

        match ($scenarioName) {
            'CRUD'            => $this->generateCRUD($tableNames),
            'Entity'          => $this->generateEntity($tableNames),
            'QueryRepository' => $this->generateQueryRepository($tableNames),
            'DTO'             => $this->generateDTO($tableNames),
            default           => null,
        };
    }

    private function askForTableNames(InputInterface $input): array
    {
        $question = new Question('Введите имя таблицы (можно несколько через запятую): ');
        /** @var string $tableNames */
        $tableNames = $this->question->ask($input, $this->output, $question);
        return array_map(trim(...), explode(',', $tableNames));
    }

    private function askForProcedureNames(InputInterface $input): array
    {
        $question = new Question('Введите имя процедуры (можно несколько через запятую): ');
        /** @var string $procNames */
        $procNames = $this->question->ask($input, $this->output, $question);
        return array_map(trim(...), explode(',', $procNames));
    }

    private function askForRepoName(InputInterface $input): string
    {
        $question = new Question('Введите имя репозитория: ');
        /** @var string $answer */
        $answer = $this->question->ask($input, $this->output, $question);
        return $answer;
    }

    private function askForDomainName(InputInterface $input): ?string
    {
        $question = new Question('Введите название домена в формате "domain.subdomain": ');
        /** @var string $domainName */
        $domainName = $this->question->ask($input, $this->output, $question);

        if (!str_contains($domainName, '.')) {
            $this->output->writeln('Название должно содержать имя домена и поддомена');
            return null;
        }

        return $domainName;
    }

    protected function buildSkeleton(InputInterface $input, string $domain, string $subdomain): void
    {
        $question = new ConfirmationQuestion('Домен не найден. Создать стандартную структуру для домена? ');
        $isAccept = $this->question->ask($input, $this->output, $question);
        if ($isAccept) {
            $domainDir = $this->projectDir . '/src/Domain/' . ucfirst($domain) . '/' . ucfirst($subdomain);
            $this->writeDir($domainDir);
            $this->writeDir("$domainDir/Entity");
            $this->writeDir("$domainDir/Repository");
            $this->writeDir("$domainDir/UseCase");
            $this->writeDir("$domainDir/Controller");
            $this->writeDir("$domainDir/DTO");
        }
    }

    protected function writeDir(string $path): void
    {
        $this->output->writeln("create dir $path...");
        mkdir($path, recursive: true);
    }

    protected function writeFile(string $file, string $content): void
    {
        $this->output->writeln("create file $file...");
        file_put_contents($file, $content);
    }

    protected function generateCRUD(array $tableNames): void
    {
        $this->generateEntity($tableNames);
        $this->generateDTO($tableNames);

        $domain = ucfirst($this->domain);
        $subdomain = ucfirst($this->subdomain);

        $scenario = new GenerateUseCase($tableNames, $this->retriever, "App\Domain\\$domain\\$subdomain\Entity");
        $scenario->load();

        foreach ($scenario->run(outputNamespace: "App\Domain\\$domain\\$subdomain\UseCase") as $fileName => $content) {
            $file = "$this->projectDir/src/Domain/$domain/$subdomain/UseCase/" . $fileName;
            $this->writeFile($file, $content);
        }

        $scenario = new GenerateController($tableNames, $this->retriever, "$this->domain.$this->subdomain");
        $scenario->load();

        foreach ($scenario->run(outputNamespace: "App\Domain\\$domain\\$subdomain\Controller") as $fileName => $content) {
            $file = "$this->projectDir/src/Domain/$domain/$subdomain/Controller/" . $fileName;
            $this->writeFile($file, $content);
        }

        // в юзкейсах могут использоваться дженерики, надо обновлять конфиг
        `php bin/refreshGenerics.php`;
    }

    // TODO: опция, чтобы генерить только обязательные поля, или как-то помечать опциональные поля
    protected function generateEntity(array $tableNames): void
    {
        $scenario = new GenerateEntity($tableNames, $this->retriever);
        $scenario->load();
        $domain = ucfirst($this->domain);
        $subdomain = ucfirst($this->subdomain);

        foreach ($scenario->run(outputNamespace: "App\Domain\\$domain\\$subdomain\Entity") as $fileName => $content) {
            $file = "$this->projectDir/src/Domain/$domain/$subdomain/Entity/" . $fileName;
            $this->writeFile($file, $content);
        }
    }

    protected function generateQueryRepository(?array $tableNames = null, ?string $repoName = null): void
    {
        $domain = ucfirst($this->domain);
        $subdomain = ucfirst($this->subdomain);

        $scenario = new GenerateQueryRepository($this->retriever, "App\Domain\\$domain\\$subdomain\Entity", $tableNames, repoName: $repoName);
        $scenario->load();

        foreach ($scenario->run(outputNamespace: "App\Domain\\$domain\\$subdomain\Repository") as $fileName => $content) {
            $file = "$this->projectDir/src/Domain/$domain/$subdomain/Repository/" . $fileName;
            $this->writeFile($file, $content);
        }
    }

    protected function generateCommandRepository(string $repoName): void
    {
        $domain = ucfirst($this->domain);
        $subdomain = ucfirst($this->subdomain);

        $scenario = new GenerateCommandRepository($repoName);
        $scenario->load();

        foreach ($scenario->run(outputNamespace: "App\Domain\\$domain\\$subdomain\Repository") as $fileName => $content) {
            $file = "$this->projectDir/src/Domain/$domain/$subdomain/Repository/" . $fileName;
            $this->writeFile($file, $content);
        }
    }

    protected function generateProcedureCall(array $procNames, string $repoName): void
    {
        $domain = ucfirst($this->domain);
        $subdomain = ucfirst($this->subdomain);

        if (class_exists($repoName) === false) {
            if (str_contains($repoName, 'QueryRepository')) {
                $this->generateQueryRepository(repoName: $repoName);
            }
            if (str_contains($repoName, 'CommandRepository')) {
                $this->generateCommandRepository($repoName);
            }
        }

        $repoClass = "App\Domain\\$domain\\$subdomain\Repository\\$repoName";
        $scenario = new GenerateProcedure($procNames, $this->retriever, $repoClass);
        $scenario->load();

        foreach ($scenario->run(outputNamespace: "App\Domain\\$domain\\$subdomain\Repository") as $fileName => $content) {
            $this->writeFile($fileName, $content);
        }
    }

    protected function generateDTO(array $tableNames): void
    {
        $domain = ucfirst($this->domain);
        $subdomain = ucfirst($this->subdomain);

        $scenario = new GenerateDTO($tableNames, $this->retriever, "App\Domain\\$domain\\$subdomain\Entity");
        $scenario->load();

        foreach ($scenario->run(outputNamespace: "App\Domain\\$domain\\$subdomain\DTO") as $fileName => $content) {
            $file = "$this->projectDir/src/Domain/$domain/$subdomain/DTO/" . $fileName;
            $this->writeFile($file, $content);
        }
    }
}
