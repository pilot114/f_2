<?php

declare(strict_types=1);

namespace App\System\Generate\Scenario;

use App\Common\Helper\EnumerableWithTotal;
use App\System\Generate\ScenarioInterface;
use Database\Schema\DbObject\DbObjectType;
use Database\Schema\DbObject\Table;
use Database\Schema\EntityRetriever;
use Generator;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Literal;
use Nette\PhpGenerator\PhpFile;

class GenerateQueryRepository implements ScenarioInterface
{
    /**
     * @var array<Table>
     */
    private array $tables = [];

    public function __construct(
        private readonly EntityRetriever $retriever,
        private readonly string          $entityNamespace,
        private readonly ?array          $tableNames = null,
        private ?string $repoName = null
    ) {
    }

    public function load(): void
    {
        if ($this->tableNames === null) {
            return;
        }

        foreach ($this->tableNames as $tableName) {
            /** @var Table $table */
            $table = $this->retriever->getDbObject($tableName, DbObjectType::Table);
            $this->tables[] = $table;
        }
    }

    public function run(string $outputNamespace): Generator
    {
        // создаем пустой репозиторий без сущностей
        if ($this->tableNames === null) {
            $class = new ClassType($this->repoName);
            $class->setExtends('Database\ORM\QueryRepository');

            $file = new PhpFile();
            $file
                ->setStrictTypes()
                ->addNamespace($outputNamespace)
                ->addUse('Database\ORM\QueryRepository')
                ->addUse('Illuminate\Support\Enumerable')
                ->add($class)
            ;

            yield $this->repoName . '.php' => (string) $file;
            return;
        }

        foreach ($this->tables as $table) {
            $entityName =
                EnumerableWithTotal::build(explode('.', str_replace('_', '.', $table->name)))
                    ->map(fn ($x): string => ucfirst(mb_strtolower($x)))
                    ->implode('')
            ;
            $repoName = $entityName . 'QueryRepository';

            $class = new ClassType($repoName);

            $class->setExtends('Database\ORM\QueryRepository');
            $class->addComment("@extends QueryRepository<$entityName>");

            $class
                ->addProperty('entityName', new Literal("$entityName::class"))
                ->setProtected()
                ->setType('string')
            ;

            $class
                ->addMethod('customFind')
                ->addComment("@return Enumerable<int, $entityName>")
                ->addBody('$sql = <<<SQL')
                ->addBody('SELECT * FROM $table->name')
                ->addBody('SQL;')
                ->addBody('return $this->query($sql);')
                ->setReturnType('Illuminate\Support\Enumerable')
            ;

            $file = new PhpFile();
            $file
                ->setStrictTypes()
                ->addNamespace($outputNamespace)
                ->addUse($this->entityNamespace . '\\' . $entityName)
                ->addUse('Database\ORM\QueryRepository')
                ->addUse('Illuminate\Support\Enumerable')
                ->add($class)
            ;

            yield $repoName . '.php' => (string) $file;
        }
    }
}
