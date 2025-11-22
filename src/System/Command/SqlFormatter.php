<?php

declare(strict_types=1);

namespace App\System\Command;

use App\System\DomainSourceCodeFinder;
use Doctrine\SqlFormatter\NullHighlighter;
use Doctrine\SqlFormatter\SqlFormatter as Formatter;
use Generator;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\CloningVisitor;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

#[AsCommand(name: 'system:sqlFormatter', description: 'форматирование SQL запросов')]
class SqlFormatter extends Command
{
    public static Shared $shared;

    public function __construct(
        private Formatter              $formatter,
        private DomainSourceCodeFinder $fileLoader,
    ) {
        parent::__construct();
        self::$shared = new Shared();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('sql', InputArgument::OPTIONAL, 'sql запрос')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var ?string $sql */
        $sql = $input->getArgument('sql');
        if ($sql !== null) {
            echo $this->formatter->format(mb_strtolower($sql));
            return Command::SUCCESS;
        }
        $this->prepareSqlCache();

        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');
        $question = new ChoiceQuestion(
            'Какой SQL запрос нужно форматировать?',
            array_keys(SqlFormatter::$shared->sqlCache),
        );
        /** @var string $ask */
        $ask = $helper->ask($input, $output, $question);
        SqlFormatter::$shared->currentKey = $ask;
        $sql = SqlFormatter::$shared->sqlCache[SqlFormatter::$shared->currentKey];
        SqlFormatter::$shared->sqlForReplace = (new Formatter(new NullHighlighter()))->format($sql);

        $this->replaceInCode();
        return Command::SUCCESS;
    }

    private function replaceInCode(): void
    {
        [$baseFileName] = explode(':', SqlFormatter::$shared->currentKey);

        foreach ($this->getQueryRepositoryFileNames() as $filename) {
            if (basename($filename) !== $baseFileName) {
                continue;
            }
            $code = file_get_contents($filename);
            if (!$code) {
                continue;
            }

            $parser = (new ParserFactory())->createForHostVersion();
            $oldStmts = $parser->parse($code);
            if ($oldStmts === null) {
                continue;
            }
            $oldTokens = $parser->getTokens();

            $traverser = new NodeTraverser(new CloningVisitor());
            $newStmts = $traverser->traverse($oldStmts);

            // modify
            $traverser->addVisitor(new class() extends NodeVisitorAbstract {
                public function enterNode(Node $node): null
                {
                    if (is_string($node->value ?? null) === false) {
                        return null;
                    }
                    $startText = mb_strtolower(substr(trim($node->value), 0, 6));
                    if ($startText === 'select' && $node->getAttribute('docLabel') === 'SQL') {
                        [$baseFileName, $startLine] = explode(':', SqlFormatter::$shared->currentKey);
                        if ($node->getStartLine() !== (int) $startLine) {
                            return null;
                        }
                        $node->value = SqlFormatter::$shared->sqlForReplace;
                    }
                    return null;
                }
            });
            $newStmts = $traverser->traverse($newStmts);

            $newCode = (new Standard())->printFormatPreserving($newStmts, $oldStmts, $oldTokens);
            file_put_contents($filename, $newCode);
        }
    }

    private function prepareSqlCache(): void
    {
        foreach ($this->getQueryRepositoryFileNames() as $filename) {
            SqlFormatter::$shared->currentFileName = $filename;
            $ast = $this->getAstByFileName($filename);
            if ($ast === null) {
                continue;
            }

            $traverser = new NodeTraverser();
            $traverser->addVisitor(new class() extends NodeVisitorAbstract {
                public function enterNode(Node $node): null
                {
                    if (is_string($node->value ?? null) === false) {
                        return null;
                    }
                    $startText = mb_strtolower(substr(trim($node->value), 0, 6));
                    // получаем все SQL запросы, которые начинаются с select
                    if ($startText === 'select' && $node->getAttribute('docLabel') === 'SQL') {
                        $name = basename(SqlFormatter::$shared->currentFileName) . ':' . $node->getStartLine();
                        SqlFormatter::$shared->sqlCache[$name] = $node->value;
                    }
                    return null;
                }
            });
            $traverser->traverse($ast);
        }
    }

    /**
     * @return Generator<string>
     */
    private function getQueryRepositoryFileNames(): Generator
    {
        foreach ($this->fileLoader->getClassReflections('*QueryRepository.php') as $queryRepository) {
            $filename = $queryRepository->getFileName();
            if ($filename) {
                yield $filename;
            }
        }
    }

    private function getAstByFileName(string $fileName): ?array
    {
        $parser = (new ParserFactory())->createForHostVersion();
        $content = file_get_contents($fileName);
        return $content ? $parser->parse($content) : null;
    }
}
