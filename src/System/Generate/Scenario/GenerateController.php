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

class GenerateController implements ScenarioInterface
{
    /**
     * @var array<Table>
     */
    private array $tables = [];

    public function __construct(
        private readonly array           $tableNames,
        private readonly EntityRetriever $retriever,
        private readonly string          $domain,
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
        $useCaseNamespace = str_replace('Controller', 'UseCase', $outputNamespace);

        foreach ($this->tables as $table) {
            $entityName =
                EnumerableWithTotal::build(explode('.', str_replace('_', '.', $table->name)))
                    ->map(fn ($x): string => ucfirst(mb_strtolower($x)))
                    ->implode('')
            ;

            // READ
            $name = "Read{$entityName}Controller";
            $class = new ClassType($name);
            $useCaseBaseName = 'Query' . basename(str_replace('\\', '/', $entityName)) . 'UseCase';
            $useCaseFullName = "$useCaseNamespace\\$useCaseBaseName";

            $class->addMethod('__construct')
                ->addPromotedParameter('useCase')
                ->setPrivate()
                ->setType($useCaseFullName)
            ;
            $this->addReadMethods($class, $entityName);

            $file = new PhpFile();
            $file
                ->setStrictTypes()
                ->addNamespace($outputNamespace)
                ->addUse($useCaseFullName)
                ->addUse('App\Common\Attribute\RpcMethod')
                ->add($class)
            ;
            yield $name . '.php' => (string) $file;

            // WRITE
            $name = "Write{$entityName}Controller";
            $class = new ClassType($name);
            $useCaseBaseName = 'Command' . basename(str_replace('\\', '/', $entityName)) . 'UseCase';
            $useCaseFullName = "$useCaseNamespace\\$useCaseBaseName";
            $class->addMethod('__construct')
                ->addPromotedParameter('useCase')
                ->setPrivate()
                ->setType($useCaseFullName)
            ;
            $this->addWriteMethods($class, $entityName);

            $file = new PhpFile();
            $file
                ->setStrictTypes()
                ->addNamespace($outputNamespace)
                ->addUse($useCaseFullName)
                ->addUse('App\Common\Attribute\RpcMethod')
                ->add($class)
            ;
            yield $name . '.php' => (string) $file;
        }
    }

    private function addReadMethods(ClassType $class, string $entityName): void
    {
        $getMethod = $class->addMethod('get');
        $getMethod->addAttribute('App\Common\Attribute\RpcMethod', [
            "$this->domain.get$entityName",
            "Получение $entityName по id",
        ]);
        $getMethod->setReturnType('array');
        $getMethod->addParameter('id')->setType('int');
        $getMethod->addBody('return $this->useCase->get($id)->toArray();');

        $findMethod = $class->addMethod('find');
        $findMethod->addAttribute('App\Common\Attribute\RpcMethod', [
            "$this->domain.find$entityName",
            "Поиск $entityName",
        ]);
        $findMethod->setReturnType('array');
        $findMethod->addBody('$params = [];');
        $findMethod->addBody('[$items, $total] = $this->useCase->find($params);');
        $findMethod->addBody('return [');
        $findMethod->addBody('\'items\' => $items->toArray(),');
        $findMethod->addBody('\'total\' => $total,');
        $findMethod->addBody('];');
    }

    private function addWriteMethods(ClassType $class, string $entityName): void
    {
        $createMethod = $class->addMethod('create');
        $createMethod->addAttribute('App\Common\Attribute\RpcMethod', [
            "$this->domain.create$entityName",
            "Создание $entityName",
        ]);
        $createMethod->setReturnType('array');
        $createMethod->addBody('$entity = $this->useCase->create();');
        $createMethod->addBody('return $entity->toArray();');

        $updateMethod = $class->addMethod('update');
        $updateMethod->addAttribute('App\Common\Attribute\RpcMethod', [
            "$this->domain.update$entityName",
            "Обновление $entityName",
        ]);
        $updateMethod->setReturnType('array');
        $updateMethod->addBody('$entity = $this->useCase->update();');
        $updateMethod->addBody('return $entity->toArray();');

        $deleteMethod = $class->addMethod('delete');
        $deleteMethod->addAttribute('App\Common\Attribute\RpcMethod', [
            "$this->domain.delete$entityName",
            "Удаление $entityName",
        ]);
        $deleteMethod->setReturnType('void');
        $deleteMethod->addParameter('id')->setType('int');
        $deleteMethod->addBody('$this->useCase->delete($id);');
    }
}
