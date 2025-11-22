<?php

declare(strict_types=1);

namespace App\System\Command;

use App\System\DomainSourceCodeFinder;
use Database\Schema\DbObject\DbObjectType;
use Database\Schema\EntityRetriever;
use Generator;
use PhpParser\Node;
use PhpParser\Node\Attribute;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\ParentConnectingVisitor;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'system:dbUsageReport', description: 'отчёт по использованию БД в поддомене')]
class DbUsageReport extends Command
{
    public static array $likeTables;
    public static array $likeProcedures;

    public function __construct(
        private DomainSourceCodeFinder $fileLoader,
        private EntityRetriever $retriever,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('subdomain', InputArgument::REQUIRED, 'поддомен (domain.subdomain)')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $subdomain */
        $subdomain = $input->getArgument('subdomain');

        foreach ($this->findFileNameInSubdomain($subdomain, '*Repository.php') as $repositoryName) {
            $code = file_get_contents($repositoryName);
            if (!$code) {
                continue;
            }
            $parser = (new ParserFactory())->createForHostVersion();
            $stmts = $parser->parse($code);

            $traverser = new NodeTraverser(new ParentConnectingVisitor());
            $traverser->addVisitor(new class() extends NodeVisitorAbstract {
                public function enterNode(Node $node): null
                {
                    $likeProc = ['procedure', 'function'];
                    if ($node instanceof MethodCall && in_array($node->name->name, $likeProc, true)) {
                        DbUsageReport::$likeProcedures[] = $node->args[0]->value->value;
                        return null;
                    }
                    $likeTable = ['count', 'max', 'min', 'sum', 'avg', 'exist', 'delete', 'insert', 'update'];
                    $isConn = isset($node->var->name->name) && $node->var->name->name === 'conn';
                    if ($node instanceof MethodCall && in_array($node->name->name, $likeTable, true) && $isConn) {
                        // echo (new \PhpParser\PrettyPrinter\Standard())->prettyPrint([$node]);
                        DbUsageReport::$likeTables[] = $node->args[0]->value->value;
                        return null;
                    }

                    if (is_string($node->value ?? null) === false) {
                        return null;
                    }

                    if ($node->getAttribute('docLabel') !== 'SQL') {
                        return null;
                    }
                    $pattern = '/(?:FROM|JOIN|UPDATE|INTO|TABLE)\s+([A-Za-z_]+.?[A-Za-z_]+)/i';
                    if (preg_match_all($pattern, $node->value, $matches)) {
                        foreach ($matches[1] as $table) {
                            DbUsageReport::$likeTables[] = $table;
                        }
                    }
                    return null;
                }
            });
            $traverser->traverse($stmts);
        }

        foreach ($this->findFileNameInSubdomain($subdomain, '*.php', 'Entity') as $entityName) {
            $code = file_get_contents($entityName);
            if (!$code) {
                continue;
            }
            $parser = (new ParserFactory())->createForHostVersion();
            $stmts = $parser->parse($code);

            $traverser = new NodeTraverser();
            $traverser->addVisitor(new class() extends NodeVisitorAbstract {
                public function enterNode(Node $node): null
                {
                    if ($node instanceof Attribute && $node->name->name === 'Entity') {
                        DbUsageReport::$likeTables[] = $node->args[0]->value->value;
                    }
                    return null;
                }
            });
            $traverser->traverse($stmts);
        }

        $tablesNames = array_values(array_unique(DbUsageReport::$likeTables));
        $procNames = array_values(array_unique(DbUsageReport::$likeProcedures));

        $tables = [];
        $procedures = [];
        foreach ($tablesNames as $tableName) {
            $tables[] = $this->retriever->get($tableName, DbObjectType::Table);
        }
        foreach ($procNames as $procedureName) {
            $procedures[] = $this->retriever->get($procedureName, DbObjectType::Procedure);
        }

        echo json_encode([
            'tables'     => $tables,
            'procedures' => $procedures,
        ], JSON_PRETTY_PRINT);

        return Command::SUCCESS;
    }

    /**
     * @return Generator<string>
     */
    private function findFileNameInSubdomain(string $subdomain, string $name, string $path = ''): Generator
    {
        $subdomain = mb_strtolower(str_replace('.', '/', $subdomain));

        foreach ($this->fileLoader->getClassReflections($name, $path) as $reflectionClass) {
            $filename = $reflectionClass->getFileName();
            if ($filename && str_contains(mb_strtolower($filename), $subdomain)) {
                yield $filename;
            }
        }
    }
}
