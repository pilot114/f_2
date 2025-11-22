<?php

declare(strict_types=1);

namespace App\System\Generate\Scenario;

use App\Common\Helper\EnumerableWithTotal;
use App\System\Generate\ScenarioInterface;
use Database\ORM\Attribute\Loader;
use Database\ORM\CommandRepositoryInterface;
use Database\ORM\QueryRepositoryInterface;
use Database\Schema\DbObject\DbObjectType;
use Database\Schema\DbObject\Table;
use Database\Schema\EntityRetriever;
use Generator;
use Illuminate\Support\Enumerable;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpFile;

class GenerateUseCase implements ScenarioInterface
{
    /**
     * @var array<Table>
     */
    private array $tables = [];

    public function __construct(
        private readonly array           $tableNames,
        private readonly EntityRetriever $retriever,
        private readonly string          $entityNamespace,
    ) {
    }

    public function load(): void
    {
        foreach ($this->tableNames as $tableName) {
            /** @var Table $table */
            $table = $this->retriever->getDbObject($tableName, DbObjectType::Table);
            $this->tables[] = $table;
        }
    }

    public function run(string $outputNamespace): Generator
    {
        foreach ($this->tables as $table) {
            $entityName = $this->entityNamespace . '\\' .
                EnumerableWithTotal::build(explode('.', str_replace('_', '.', $table->name)))
                    ->map(fn ($x): string => ucfirst(mb_strtolower($x)))
                    ->implode('')
            ;
            $baseEntityClassName = basename(str_replace('\\', '/', $entityName));

            // COMMAND
            $name = 'Command' . $baseEntityClassName . 'UseCase';
            $class = new ClassType($name);

            $class->addMethod('__construct')
                ->addPromotedParameter('repository')
                ->setPrivate()
                ->setType(CommandRepositoryInterface::class)
                ->addComment("@var CommandRepositoryInterface<$baseEntityClassName>")
            ;

            $requiredColumnNames = [];
            foreach ($table->sortColumnsByOptional() as $column) {
                if ($column->name === 'id') {
                    continue;
                }
                $phpName =
                    EnumerableWithTotal::build(explode('_', $column->name))
                        ->map(fn ($x): string => ucfirst(mb_strtolower($x)))
                        ->implode('')
                ;
                $phpName = lcfirst($phpName);

                $requiredColumnNames[] = $phpName . ': ' . match ($column->type) {
                    'integer', 'float' => '0',
                    'date'  => 'new DateTimeImmutable()',
                    default => "''",
                };
                if ($column->isNull === true) {
                    break;
                }
            }
            $requiredColumnNames = implode(",\n", $requiredColumnNames);

            $class->addMethod('create')
                ->setPublic()
                ->setReturnType($entityName)
                ->addBody("\$entity = new $baseEntityClassName(id: Loader::ID_FOR_INSERT,\n$requiredColumnNames);")
                ->addBody('return $this->repository->create($entity);')
            ;
            $class->addMethod('update')
                ->setPublic()
                ->setReturnType($entityName)
                ->addBody("\$entity = new $baseEntityClassName(id: \$id,\n$requiredColumnNames);")
                ->addBody('return $this->repository->update($entity);')
                ->addParameter('id')->setType('int')
            ;
            $class->addMethod('delete')
                ->setPublic()
                ->setReturnType('void')
                ->setBody('$this->repository->delete($id);')
                ->addParameter('id')->setType('int')
            ;

            $file = new PhpFile();
            $file
                ->setStrictTypes()
                ->addNamespace($outputNamespace)
                ->addUse(CommandRepositoryInterface::class)
                ->addUse($entityName)
                ->addUse(Loader::class)
                ->add($class)
            ;

            yield $name . '.php' => (string) $file;

            // QUERY
            $name = 'Query' . $baseEntityClassName . 'UseCase';
            $class = new ClassType($name);

            $class->addMethod('__construct')
                ->addPromotedParameter('repository')
                ->setPrivate()
                ->setType(QueryRepositoryInterface::class)
                ->addComment("@var QueryRepositoryInterface<$baseEntityClassName>")
            ;

            $body = sprintf('return $this->repository->findOrFail($id, "Не найден %s");', $baseEntityClassName);
            $class->addMethod('get')
                ->setPublic()
                ->setReturnType($entityName)
                ->setBody($body)
                ->addParameter('id')->setType('int')
            ;

            $method = $class->addMethod('find');
            $method->addComment("@return array{0: Enumerable<int, $baseEntityClassName>, 1:int}");
            $method
                ->setPublic()
                ->setReturnType('array')
                ->addBody('$items = $this->repository->findBy($params, page: 1, perPage: 100);')
                ->addBody('$total = $this->repository->count($params);')
                ->addBody('return [$items, $total];')
                ->addParameter('params')->setType('array')
            ;

            $file = new PhpFile();
            $file
                ->setStrictTypes()
                ->addNamespace($outputNamespace)
                ->addUse(QueryRepositoryInterface::class)
                ->addUse(Enumerable::class)
                ->addUse($entityName)
                ->add($class)
            ;

            yield $name . '.php' => (string) $file;
        }
    }
}
