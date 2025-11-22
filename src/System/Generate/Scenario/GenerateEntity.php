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

class GenerateEntity implements ScenarioInterface
{
    /**
     * @var array<Table>
     */
    private array $tables = [];

    public function __construct(
        private readonly array           $tableNames,
        private readonly EntityRetriever $retriever,
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
            $entityName =
                EnumerableWithTotal::build(explode('.', str_replace('_', '.', $table->name)))
                    ->map(fn ($x): string => ucfirst(mb_strtolower($x)))
                    ->implode('')
            ;

            $class = new ClassType($entityName);

            $class->addAttribute('Database\ORM\Attribute\Entity', [
                'name' => $table->name,
            ]);
            if ($table->comment !== null) {
                $class->addComment($table->comment);
            }

            $method = $class->addMethod('__construct');

            $propertyNames = [];
            foreach ($table->sortColumnsByOptional() as $column) {
                $phpName =
                    EnumerableWithTotal::build(explode('_', $column->name))
                        ->map(fn ($x): string => ucfirst(mb_strtolower($x)))
                        ->implode('')
                ;
                $phpName = lcfirst($phpName);

                $propertyNames[] = $phpName;

                $nullablePrefix = $column->isNull ? '?' : '';

                $parameter = $method
                    ->addPromotedParameter($phpName)
                    ->setPrivate()
                    ->setType($nullablePrefix . $column->getPhpType())
                ;
                if ($column->isNull) {
                    $parameter->setDefaultValue(null);
                }

                $attrParams = [
                    'name' => $column->name,
                ];
                if ($table->comment !== null) {
                    $attrParams['comment'] = $column->comment;
                }
                $parameter->addAttribute('Database\ORM\Attribute\Column', $attrParams);
            }

            $propertyNames = implode(",\n", array_map(
                static fn ($phpName): string => "'$phpName' => \$this->{$phpName}",
                $propertyNames
            ));

            $class->addMethod('toArray')
                ->setBody("return [\n$propertyNames\n];")
                ->setReturnType('array')
            ;

            $file = new PhpFile();
            $file
                ->setStrictTypes()
                ->addNamespace($outputNamespace)
                ->addUse('DateTimeImmutable')
                ->addUse('Database\ORM\Attribute\Column')
                ->addUse('Database\ORM\Attribute\Entity')
                ->add($class)
            ;

            yield $entityName . '.php' => (string) $file;
        }
    }
}
