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
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PromotedParameter;
use ReflectionClass;

class GenerateDTO implements ScenarioInterface
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

            /** @var class-string $entityName */
            $fileName = (string) (new ReflectionClass($entityName))->getFileName();
            $file = PhpFile::fromCode((string) file_get_contents($fileName));

            $entityClass = $file->getClasses()[$entityName];

            /** @var array<PromotedParameter> $entityConstructorParams */
            $entityConstructorParams = $entityClass->getMethod('__construct')->getParameters();

            $baseEntityClassName = basename(str_replace('\\', '/', $entityName));

            // REQUEST
            $requestClassName = $baseEntityClassName . 'Request';
            $class = new ClassType($requestClassName);

            $method = $class->setReadOnly()
                ->addMethod('__construct');
            foreach ($entityConstructorParams as $param) {
                $param->setPublic()->setAttributes([]);
            }
            $method->setParameters($entityConstructorParams);

            $file = new PhpFile();
            $file
                ->setStrictTypes()
                ->addNamespace($outputNamespace)
                ->addUse('DateTimeImmutable')
                ->addUse('App\Common\Attribute\RpcParam')
                ->add($class)
            ;
            yield $requestClassName . '.php' => (string) $file;

            //// RESPONSE
            $responseClassName = $baseEntityClassName . 'Response';
            $class = new ClassType($responseClassName);

            $method = $class->setReadOnly()
                ->addMethod('__construct')
                ->setPrivate()
            ;
            foreach ($entityConstructorParams as $param) {
                $param->setPublic()->setAttributes([]);
            }
            $method->setParameters($entityConstructorParams);

            $class->setReadOnly()
                ->addMethod('build')
                ->setStatic()
                ->setReturnType('self')
                ->addBody('return new self(...$entity->toArray());')
                ->addParameter('entity')
                ->setType($entityName)
            ;

            $file = new PhpFile();
            $file
                ->setStrictTypes()
                ->addNamespace($outputNamespace)
                ->addUse('DateTimeImmutable')
                ->addUse($entityName)
                ->addUse('App\Common\Attribute\RpcParam')
                ->add($class)
            ;
            yield $responseClassName . '.php' => (string) $file;
        }
    }
}
