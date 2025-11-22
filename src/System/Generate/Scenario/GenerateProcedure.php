<?php

declare(strict_types=1);

namespace App\System\Generate\Scenario;

use App\Common\Helper\EnumerableWithTotal;
use App\System\Generate\ScenarioInterface;
use Database\Schema\DbObject\DbObjectType;
use Database\Schema\DbObject\Proc;
use Database\Schema\EntityRetriever;
use Generator;
use Nette\PhpGenerator\Method;
use Nette\PhpGenerator\PhpFile;
use ReflectionClass;

class GenerateProcedure implements ScenarioInterface
{
    /**
     * @var array<Proc>
     */
    private array $procedures = [];

    public function __construct(
        private readonly array           $procNames,
        private readonly EntityRetriever $retriever,
        private readonly string          $repoClass,
        private readonly ?string         $entityClass = null,
    ) {
    }

    public function load(): void
    {
        foreach ($this->procNames as $procName) {
            /** @var Proc $proc */
            $proc = $this->retriever->getDbObject($procName, DbObjectType::Procedure);
            $this->procedures[] = $proc;
        }
    }

    public function run(string $outputNamespace): Generator
    {
        foreach ($this->procedures as $procedure) {
            /** @var class-string $repoClass */
            $repoClass = $this->repoClass;
            $fileName = (string) (new ReflectionClass($repoClass))->getFileName();
            $file = PhpFile::fromCode((string) file_get_contents($fileName));

            $class = $file->getClasses()[$this->repoClass];

            $procName =
                EnumerableWithTotal::build(explode('.', str_replace('_', '.', $procedure->name)))
                    ->map(fn ($x): string => ucfirst(mb_strtolower($x)))
                    ->implode('')
            ;

            $method = $class
                ->removeMethod(lcfirst($procName))
                ->addMethod(lcfirst($procName));

            // TODO: если процедура обновляет сущность
            if ($this->entityClass) {
                yield $fileName => (string) $file;
                continue;
            }

            foreach ($procedure->params as $paramName => $param) {
                if ($param->isIn) {

                    $phpType = $param->getDefaultValue() === null
                        ? '?' . $param->getPhpType()
                        : $param->getPhpType()
                    ;

                    $p = $method
                        ->addParameter($param->prepareName($paramName))
                        ->setType($phpType)
                    ;
                    if ($param->hasDefault) {
                        $p->setDefaultValue($param->getDefaultValue());
                    }
                    if ($param->comment !== null) {
                        $p->addComment($param->comment);
                    }
                }
            }

            $method = $method->addBody("\$this->conn->procedure('{$procedure->getFullName()}', [");
            $hasOut = false;

            foreach ($procedure->params as $paramName => $param) {
                if ($param->isIn) {
                    $prepareParamName = $param->prepareName($paramName);
                    $method->addBody("    '$paramName' => \$$prepareParamName,");
                } else {
                    $hasOut = true;
                    $method->addBody("    '$paramName' => null,");
                }
            }
            $method->addBody("], [");
            foreach ($procedure->params as $paramName => $param) {
                $inOutType = match (true) {
                    $param->isIn && $param->isOut => 'IN_OUT',
                    $param->isIn                  => 'IN',
                    default                       => 'OUT',
                };
                $type = $param->getParamType();
                $method->addBody("    '$paramName' => [ParamMode::$inOutType, ParamType::$type],");
            }
            $method->addBody("]);");

            $file
                ->addNamespace($outputNamespace)
                ->addUse('Database\Connection\ParamMode')
                ->addUse('Database\Connection\ParamType')
            ;

            if ($hasOut) {
                $method->setReturnType('array');
                $method->setBody('return ' . $method->getBody());
            } else {
                $method->setReturnType('void');
            }
            $this->addCommentToMethodFromProcedure($method, $procedure);

            yield $fileName => (string) $file;
        }
    }

    private function addCommentToMethodFromProcedure(Method $method, Proc $procedure): void
    {
        $comment = [];
        $comment[] = "@procedure {$procedure->getFullName()}";
        if ($procedure->comment !== '') {
            $comment[] = "@comment $procedure->comment";
        }
        foreach ($procedure->errors as $error) {
            $comment[] = "@error $error->code: $error->message";
        }
        $comment[] = "\n```sql\n$procedure->body\n```";
        $method->addComment(implode("\n", $comment));
    }
}
